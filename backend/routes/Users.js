// routes/users.js
const express = require('express');
const router = express.Router();
const User = require('../models/User');
const bcrypt = require('bcryptjs');

// Get all users
router.get('/', async (req, res) => {
  try {
    const users = await User.find().select('-password').populate('role_id', 'name');
    res.json(users);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Get one user
router.get('/:id', async (req, res) => {
  try {
    const user = await User.findById(req.params.id).select('-password').populate('role_id', 'name');
    if (!user) return res.status(404).json({ message: 'User not found' });
    res.json(user);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

// Create user
router.post('/', async (req, res) => {

  try {
    const existingUser = await User.findOne({ email: req.body.email });
        if (existingUser) {
            return res.status(400).json({ message: 'Email already in use' });
        }

        // Hash the password
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(req.body.password, salt);

        // Create new user
        const user = new User({
            email: req.body.email,
            password: hashedPassword,
            name: req.body.name,
            gender: req.body.gender,
            phone: req.body.phone,
            role_id: req.body.role_id,
            profile: req.body.profile,
            status: req.body.status ?? true
        });

        const newUser = await user.save();
        
        // Don't return the password
        const userResponse = { ...newUser._doc };
        delete userResponse.password;
        
        res.status(201).json(userResponse);
    } catch (error) {
        res.status(400).json({ message: error.message });
    }

});

// Update user
router.put('/:id', async (req, res) => {
  try {
    const user = await User.findById(req.params.id);
    if (!user) return res.status(404).json({ message: 'User not found' });
    
    if (req.body.name) user.name = req.body.name;
    if (req.body.email) {
      if (req.body.email !== user.email) {
        const existingUser = await User.findOne({ email: req.body.email });
        if (existingUser) {
          return res.status(400).json({ message: 'Email already in use' });
        }
      }
      user.email = req.body.email
    }

    if (req.body.gender) user.gender = req.body.gender;
    if (req.body.phone) user.phone = req.body.phone;
    if (req.body.role_id) user.role_id = req.body.role_id;
    if (req.body.profile) user.profile = req.body.profile;
    if (req.body.status) user.status = req.body.status;

    if (req.body.password) {
      const salt = await bcrypt.genSalt(10);
      const hashedPassword = await bcrypt.hash(req.body.password, salt);
      user.password = hashedPassword;
    }
    
    const updatedUser = await user.save();
    const userResponse = { ...updatedUser._doc };
    delete userResponse.password;

    res.json(userResponse);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
});

// Delete user
router.delete('/:id', async (req, res) => {
  try {
    const user = await User.findById(req.params.id);
    if (!user) return res.status(404).json({ message: 'User not found' });
    
    await User.deleteOne({ _id: req.params.id });
    res.json({ message: 'User deleted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
});

module.exports = router;