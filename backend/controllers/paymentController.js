const Registration = require('../models/Registration');
const SessionRegistration = require('../models/sessionRegistration');
const User = require('../models/User');
const Event = require('../models/Event');
const Session = require('../models/Session');

// Get all registrations for finance approval with session details
exports.getAllPayments = async (req, res) => {
  try {
    const { status, event_id } = req.query;
    
    let filter = {};
    if (status) filter.payment_status = status;
    if (event_id) filter.event_id = event_id;

    // Get registrations with basic info
    const registrations = await Registration.find(filter)
      .populate('user_id', 'name email phone')
      .populate('event_id', 'name')
      .populate('payment_verified_by', 'name')
      .sort({ createdAt: -1 });

    // For each registration, get the associated sessions
    const registrationsWithSessions = await Promise.all(
      registrations.map(async (registration) => {
        // Get session registrations for this main registration
        const sessionRegistrations = await SessionRegistration.find({
          registration_id: registration._id
        }).populate('session_id', 'title session_fee description date start_time end_time');

        // Extract session details
        const sessions = sessionRegistrations.map(sr => sr.session_id);

        return {
          ...registration.toObject(),
          sessions: sessions
        };
      })
    );

    res.json(registrationsWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get registration by ID with session details
exports.getPaymentById = async (req, res) => {
  try {
    const registration = await Registration.findById(req.params.id)
      .populate('user_id', 'name email phone')
      .populate('event_id', 'name')
      .populate('payment_verified_by', 'name');
      
    if (!registration) {
      return res.status(404).json({ message: 'Registration not found' });
    }

    // Get associated sessions
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registration._id
    }).populate('session_id', 'title session_fee description date start_time end_time location speaker');

    const sessions = sessionRegistrations.map(sr => sr.session_id);

    const registrationWithSessions = {
      ...registration.toObject(),
      sessions: sessions,
      session_registrations: sessionRegistrations
    };
    
    res.json(registrationWithSessions);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Calculate total session fees for a registration
const calculateTotalSessionFees = async (registrationId) => {
  try {
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: registrationId
    }).populate('session_id', 'session_fee');

    return sessionRegistrations.reduce((total, sr) => {
      return total + (sr.session_id.session_fee || 0);
    }, 0);
  } catch (error) {
    console.error('Error calculating session fees:', error);
    return 0;
  }
};

// Update payment status (for finance)
exports.updatePaymentStatus = async (req, res) => {
  try {
    const { payment_status, rejection_reason } = req.body;
    const financeUserId = req.user._id; 
    
    const registration = await Registration.findById(req.params.id);
    if (!registration) {
      return res.status(404).json({ message: 'Registration not found' });
    }

    // Validate payment status
    if (!['pending', 'approved', 'rejected'].includes(payment_status)) {
      return res.status(400).json({ message: 'Invalid payment status' });
    }

    // If rejecting, rejection reason is required
    if (payment_status === 'rejected' && !rejection_reason) {
      return res.status(400).json({ message: 'Rejection reason is required when rejecting payment' });
    }

    // Calculate expected total from sessions
    const expectedTotal = await calculateTotalSessionFees(registration._id);

    // Optional: Validate payment amount against session fees
    if (payment_status === 'approved' && registration.payment_amount) {
      const paymentAmount = registration.payment_amount;
      const difference = Math.abs(paymentAmount - expectedTotal);
      
      // You can add tolerance for small differences or require exact match
      // For now, we'll just log it but still approve
      if (difference > 0) {
        console.log(`Payment amount difference: Expected ${expectedTotal}, Paid ${paymentAmount}, Difference: ${difference}`);
      }
    }

    // Update registration
    registration.payment_status = payment_status;
    registration.payment_verified_by = financeUserId;
    registration.payment_verified_at = new Date();
    
    if (payment_status === 'rejected') {
      registration.rejection_reason = rejection_reason;
      registration.registration_status = 'cancelled';
      
      // Also update session registrations to cancelled
      await SessionRegistration.updateMany(
        { registration_id: registration._id },
        { status: 'cancelled' }
      );
    } else if (payment_status === 'approved') {
      registration.registration_status = 'confirmed';
      registration.rejection_reason = null; // Clear any previous rejection reason
      
      // Update session registrations to registered/confirmed
      await SessionRegistration.updateMany(
        { registration_id: registration._id },
        { status: 'registered' }
      );
    }

    const updatedRegistration = await registration.save();
    
    // Populate untuk response
    await updatedRegistration.populate([
      { path: 'user_id', select: 'name email phone' },
      { path: 'event_id', select: 'name' },
      { path: 'payment_verified_by', select: 'name' }
    ]);

    // Get sessions for response
    const sessionRegistrations = await SessionRegistration.find({
      registration_id: updatedRegistration._id
    }).populate('session_id', 'title session_fee');

    const response = {
      ...updatedRegistration.toObject(),
      sessions: sessionRegistrations.map(sr => sr.session_id),
      expected_total: expectedTotal
    };

    res.json({
      message: `Payment ${payment_status} successfully`,
      registration: response
    });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Get pending payments count (for dashboard)
exports.getPendingPaymentsCount = async (req, res) => {
  try {
    const count = await Registration.countDocuments({ payment_status: 'pending' });
    res.json({ count });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Bulk approve payments
exports.bulkApprovePayments = async (req, res) => {
  try {
    const { registration_ids } = req.body;
    const financeUserId = req.user._id;

    if (!Array.isArray(registration_ids) || registration_ids.length === 0) {
      return res.status(400).json({ message: 'Registration IDs array is required' });
    }

    // Update main registrations
    const result = await Registration.updateMany(
      { 
        _id: { $in: registration_ids },
        payment_status: 'pending'
      },
      {
        payment_status: 'approved',
        registration_status: 'confirmed',
        payment_verified_by: financeUserId,
        payment_verified_at: new Date(),
        $unset: { rejection_reason: 1 }
      }
    );

    // Update associated session registrations
    await SessionRegistration.updateMany(
      { registration_id: { $in: registration_ids } },
      { status: 'registered' }
    );

    res.json({
      message: `${result.modifiedCount} payments approved successfully`,
      modified_count: result.modifiedCount
    });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Get payment statistics
exports.getPaymentStats = async (req, res) => {
  try {
    const stats = await Registration.aggregate([
      {
        $group: {
          _id: '$payment_status',
          count: { $sum: 1 },
          total_amount: { $sum: '$payment_amount' }
        }
      }
    ]);

    // Calculate total session fees for all registrations
    const allRegistrations = await Registration.find().select('_id');
    let totalExpectedAmount = 0;
    
    for (const reg of allRegistrations) {
      const sessionTotal = await calculateTotalSessionFees(reg._id);
      totalExpectedAmount += sessionTotal;
    }

    const formattedStats = {
      by_status: stats.reduce((acc, stat) => {
        acc[stat._id] = {
          count: stat.count,
          total_amount: stat.total_amount || 0
        };
        return acc;
      }, {}),
      total_expected_amount: totalExpectedAmount
    };

    res.json(formattedStats);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};