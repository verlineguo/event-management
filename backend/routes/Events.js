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



// Update only the event status
router.patch('/:id/status', eventController.updateEventStatus);


module.exports = router;
