const express = require('express');
const router = express.Router();
const profileController = require('../controllers/profileController');
const authMiddleware = require('../middleware/auth'); // Asumsikan Anda punya middleware auth

// Apply auth middleware to all profile routes
router.use(authMiddleware);

// Profile routes
router.get('/:id', profileController.getProfile);
router.put('/:id', profileController.updateProfile);
router.put('/:id/password', profileController.updatePassword);
router.get('/me', profileController.getCurrentUser); // For getting current logged in user

module.exports = router;