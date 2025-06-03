const Registration = require('../models/Registration');
const Event = require('../models/Events');
const User = require('../models/Users');

// Get all registrations with pagination and filters
exports.getAllRegistrations = async (req, res) => {
  try {
    const { 
      page = 1, 
      limit = 10, 
      payment_status, 
      event_id, 
      registration_status 
    } = req.query;

    const filter = {};
    if (payment_status) filter.payment_status = payment_status;
    if (event_id) filter.event_id = event_id;
    if (registration_status) filter.registration_status = registration_status;

    const registrations = await Registration.find(filter)
      .populate('event_id', 'name registration_fee')
      .populate('user_id', 'name email phone')
      .populate('payment_verified_by', 'name')
      .sort({ createdAt: -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Registration.countDocuments(filter);

    res.json({
      registrations,
      totalPages: Math.ceil(total / limit),
      currentPage: page,
      total
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get registration by ID
exports.getRegistrationById = async (req, res) => {
  try {
    const registration = await Registration.findById(req.params.id)
      .populate('event_id', 'name registration_fee description')
      .populate('user_id', 'name email phone')
      .populate('payment_verified_by', 'name');
      
    if (!registration) {
      return res.status(404).json({ message: 'Registration not found' });
    }
    
    res.json(registration);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Update payment status (for finance role)
exports.updatePaymentStatus = async (req, res) => {
  try {
    const { payment_status, rejection_reason } = req.body;
    const verifiedBy = req.user.id; // Assuming user info is in req.user from auth middleware

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
    registration.payment_verified_by = verifiedBy;
    registration.payment_verified_at = new Date();
    
    if (payment_status === 'rejected') {
      registration.rejection_reason = rejection_reason;
      registration.registration_status = 'cancelled';
    } else if (payment_status === 'approved') {
      registration.registration_status = 'confirmed';
      registration.rejection_reason = undefined; // Clear rejection reason if previously rejected
    }

    const updatedRegistration = await registration.save();
    
    // Populate the response
    await updatedRegistration.populate('event_id', 'name registration_fee');
    await updatedRegistration.populate('user_id', 'name email phone');
    await updatedRegistration.populate('payment_verified_by', 'name');

    res.json({
      message: `Payment ${payment_status} successfully`,
      registration: updatedRegistration
    });
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Get registrations pending for payment verification
exports.getPendingPayments = async (req, res) => {
  try {
    const { page = 1, limit = 10 } = req.query;

    const registrations = await Registration.find({ 
      payment_status: 'pending',
      payment_proof_url: { $exists: true, $ne: null }
    })
      .populate('event_id', 'name registration_fee')
      .populate('user_id', 'name email phone')
      .sort({ createdAt: -1 })
      .limit(limit * 1)
      .skip((page - 1) * limit);

    const total = await Registration.countDocuments({ 
      payment_status: 'pending',
      payment_proof_url: { $exists: true, $ne: null }
    });

    res.json({
      registrations,
      totalPages: Math.ceil(total / limit),
      currentPage: page,
      total
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get payment statistics
exports.getPaymentStatistics = async (req, res) => {
  try {
    const stats = await Registration.aggregate([
      {
        $group: {
          _id: '$payment_status',
          count: { $sum: 1 },
          totalAmount: { $sum: '$payment_amount' }
        }
      }
    ]);

    const totalRegistrations = await Registration.countDocuments();
    
    res.json({
      statistics: stats,
      totalRegistrations
    });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};