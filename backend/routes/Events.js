const express = require('express');
const router = express.Router();
const eventController = require('../controllers/eventController');

// Get all events
router.get('/', eventController.getAllEvents);

// Get event by ID with sessions
router.get('/:id', eventController.getEventById);

// Create a new event with sessions
router.post('/', eventController.createEvent);

// Update event and its sessions
router.put('/:id', eventController.updateEvent);

// Delete event and related sessions
router.delete('/:id', eventController.deleteEvent);

// Get events by category
router.get('/category/:categoryId', eventController.getEventsByCategory);

// Get events by status
router.get('/status/:status', eventController.getEventsByStatus);

// Get sessions for an event
router.get('/:id/sessions', eventController.getEventSessions);

// Update only the event status
router.patch('/:id/status', eventController.updateEventStatus);

// Get QR code for an event
router.get('/:id/qrcode', eventController.getEventQRCode);

// Search events (query: ?q=searchText&category=...&status=...)
router.get('/search/query', eventController.searchEvents);

module.exports = router;
