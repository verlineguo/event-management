const express = require('express');
const router = express.Router();
const Role = require('../models/Role');

router.get('/', async (req, res) => {
  try {
    const roles = await Role.find().select('_id name');
    res.json(roles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
});

module.exports = router;