const Registration = require('../models/Registration');
const User = require('../models/User');
const Event = require('../models/Event');

// Get all registrations for finance approval
exports.getAllPayments = async (req, res) => {
  try {
    const { status, event_id } = req.query;
    
    let filter = {};
    if (status) filter.payment_status = status;
    if (event_id) filter.event_id = event_id;

    const registrations = await Registration.find(filter)
      .populate('user_id', 'name email phone')
      .populate('event_id', 'name registration_fee')
      .populate('payment_verified_by', 'name')
      .sort({ createdAt: -1 });

    res.json(registrations);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get registration by ID
exports.getPaymentById = async (req, res) => {
  try {
    const registration = await Registration.findById(req.params.id)
      .populate('user_id', 'name email phone')
      .populate('event_id', 'name registration_fee')
      .populate('payment_verified_by', 'name');
      
    if (!registration) {
      return res.status(404).json({ message: 'Registration not found' });
    }
    
    res.json(registration);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Update payment status (for finance)
exports.updatePaymentStatus = async (req, res) => {
  try {
    const { payment_status, rejection_reason } = req.body;
    const financeUserId = req.user._id; // Dari JWT token
    
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

    // Update registration
    registration.payment_status = payment_status;
    registration.payment_verified_by = financeUserId;
    registration.payment_verified_at = new Date();
    
    if (payment_status === 'rejected') {
      registration.rejection_reason = rejection_reason;
      registration.registration_status = 'cancelled';
    } else if (payment_status === 'approved') {
      registration.registration_status = 'confirmed';
      registration.rejection_reason = null; // Clear any previous rejection reason
    }

    const updatedRegistration = await registration.save();
    
    // Populate untuk response
    await updatedRegistration.populate([
      { path: 'user_id', select: 'name email phone' },
      { path: 'event_id', select: 'name registration_fee' },
      { path: 'payment_verified_by', select: 'name' }
    ]);

    res.json({
      message: `Payment ${payment_status} successfully`,
      registration: updatedRegistration
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
    const financeUserId = req.user.id;

    if (!Array.isArray(registration_ids) || registration_ids.length === 0) {
      return res.status(400).json({ message: 'Registration IDs array is required' });
    }

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

    res.json({
      message: `${result.modifiedCount} payments approved successfully`,
      modified_count: result.modifiedCount
    });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};