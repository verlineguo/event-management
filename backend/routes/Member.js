const express = require('express');
const router = express.Router();
const memberController = require('../controllers/memberController');


router.get('/featured', memberController.getFeaturedEvents);
router.get('/search', memberController.searchEvents);
router.get('/:id', memberController.getEventDetail);
router.get('/registration', memberController.getEventRegistration);
module.exports = router;
