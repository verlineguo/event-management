const express = require('express');
const router = express.Router();
const AttendanceController = require('../controllers/attendanceController');

router.post('/scan-qr', AttendanceController.scanQRCode);
router.post('/manual-checkin', AttendanceController.manualCheckIn);
router.get('/session/:sessionId', AttendanceController.getSessionAttendance);
router.get('/events/:eventId', AttendanceController.getEventParticipants);
router.get('/scanned-participants/:eventId', AttendanceController.getScannedParticipants);
router.get('/participants/:participantId/details', AttendanceController.getParticipantDetails);

module.exports = router;
