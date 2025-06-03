const express = require('express');
const router = express.Router();
const paymentController = require('../controllers/paymentController');
const authenticateToken = require('../middleware/auth');

router.get('/', authenticateToken, paymentController.getAllPayments); 
router.get('/pending-count', authenticateToken, paymentController.getPendingPaymentsCount);
router.get('/:id',authenticateToken, paymentController.getPaymentById);
router.put('/:id/payment-status', authenticateToken, paymentController.updatePaymentStatus);
router.post('/bulk-approve', authenticateToken, paymentController.bulkApprovePayments);

module.exports = router;
