const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');
const authenticateToken = require('../middleware/auth');

router.post('/register', authController.register);
router.post('/login', authController.login);
router.get('/user-role', authenticateToken, authController.getUserRole);
router.get('/verify-token', authenticateToken, authController.verifyToken);
router.get('/profile', authenticateToken, authController.getProfile);

module.exports = router;
