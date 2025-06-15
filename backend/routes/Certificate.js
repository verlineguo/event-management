const express = require('express');
const router = express.Router();
const certificateController = require('../controllers/certificateController');
const authenticateToken = require('../middleware/auth');


router.get('/my-certificates',authenticateToken, certificateController.getMyCertificates);

// Committee/Admin routes - manage certificate
router.post('/sessions/:sessionId/certificate', certificateController.uploadCertificate);
router.get('/download/:participantId', authenticateToken, certificateController.downloadCertificate);
router.post('/sessions/:sessionId/bulk-certificates', authenticateToken, certificateController.bulkUploadCertificates);
router.delete('/certificates/revoke/:participantId', certificateController.revokeCertificate);
router.get('/sessions/:sessionId/certificates', certificateController.getSessionCertificates);
router.get('/sessions/:sessionId/attendance-with-certificates', certificateController.getSessionAttendanceWithCertificates);
router.get('/sessions/:sessionId/export', certificateController.exportSessionAttendance);
router.delete('/revoke/:participantId', certificateController.revokeCertificate);



module.exports = router;

