
// Admin routes (require admin role)
const { isAdmin } = require('../middleware/auth');

// Admin: Confirm payment and registration
router.patch('/:id/confirm', isAdmin, registrationController.confirmPayment);

// Admin: Reject payment
router.patch('/:id/reject', isAdmin, registrationController.rejectPayment);

// Scan QR Code (for event check-in) - bisa admin atau staff
router.post('/scan-qr', registrationController.scanQRCode);
