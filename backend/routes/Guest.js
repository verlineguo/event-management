const express = require('express');
const router = express.Router();
const path = require('path');

// Home route
router.get('/home', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend/public/guest/html/index.html'));
});

// About route
router.get('/events', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend/public/guest/html/about.html'));
});

module.exports = router;
