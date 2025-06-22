const express = require('express');
const router = express.Router();
const dashboardController = require('../controllers/dashboardController');

router.get('/admin-dashboard', dashboardController.getAdminDashboardStats);
router.get('/committee-dashboard', dashboardController.getCommitteeDashboardStats);

module.exports = router;
