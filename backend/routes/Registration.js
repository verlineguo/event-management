const express = require('express');
const router = express.Router();
const registrationController = require('../controllers/registrationController');
const authenticateToken = require('../middleware/auth');

router.get('/check/:id', authenticateToken, registrationController.checkRegistration); // Get all registrations

// Get user's registrations
router.get('/my-registrations', authenticateToken, registrationController.getMyRegistrations);

// Get single registration detail
router.get('/:id',authenticateToken, registrationController.getRegistration);

// Create new registration
router.post('/', authenticateToken, registrationController.createRegistration);

// Get QR codes for confirmed registration
router.get('/:id/qr-codes', authenticateToken, registrationController.getQRCodes);

// Draft registration routes
router.post('/draft', authenticateToken, registrationController.saveDraft);
router.get('/draft/:id',authenticateToken, registrationController.getDraft);
router.delete('/draft/:id',authenticateToken, registrationController.deleteDraft);

// Cancel registration (before confirmation)
router.patch('/:id/cancel', authenticateToken, registrationController.cancelRegistration);


module.exports = router;
