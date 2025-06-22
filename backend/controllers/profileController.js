const User = require('../models/User');
const bcrypt = require('bcryptjs');

// Get user profile by ID
exports.getProfile = async (req, res) => {
  try {
    const userId = req.params.id;
    const user = await User.findById(userId)
      .select('-password')
      .populate('role_id', 'name');
    
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    res.json(user);
  } catch (err) {
    console.error('Error fetching profile:', err);
    res.status(500).json({ message: 'Server error while fetching profile' });
  }
};

// Update user profile (name, gender, phone - NOT password)
exports.updateProfile = async (req, res) => {
  try {
    const userId = req.params.id;
    const { name, gender, phone } = req.body;
    
    // Find user
    const user = await User.findById(userId);
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    // Update only profile fields
    if (name !== undefined) user.name = name;
    if (gender !== undefined) user.gender = gender;
    if (phone !== undefined) user.phone = phone;
    
    // Save updated user
    const updatedUser = await user.save();
    
    // Return user without password
    const userResponse = await User.findById(updatedUser._id)
      .select('-password')
      .populate('role_id', 'name');
    
    res.json(userResponse);
  } catch (err) {
    console.error('Error updating profile:', err);
    res.status(400).json({ message: err.message || 'Error updating profile' });
  }
};

// Update user password
exports.updatePassword = async (req, res) => {
  try {
    const userId = req.params.id;
    const { current_password, new_password } = req.body;
    
    // Validate input
    if (!current_password || !new_password) {
      return res.status(400).json({ 
        message: 'Current password and new password are required' 
      });
    }
    
    if (new_password.length < 6) {
      return res.status(400).json({ 
        message: 'New password must be at least 6 characters long' 
      });
    }
    
    // Find user with password
    const user = await User.findById(userId);
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    // Verify current password
    const isCurrentPasswordValid = await bcrypt.compare(current_password, user.password);
    if (!isCurrentPasswordValid) {
      return res.status(400).json({ message: 'Current password is incorrect' });
    }
    
    // Hash new password
    const salt = await bcrypt.genSalt(10);
    const hashedNewPassword = await bcrypt.hash(new_password, salt);
    
    // Update password
    user.password = hashedNewPassword;
    await user.save();
    
    res.json({ message: 'Password updated successfully' });
  } catch (err) {
    console.error('Error updating password:', err);
    res.status(500).json({ message: 'Server error while updating password' });
  }
};

// Get current user info (for authenticated user)
exports.getCurrentUser = async (req, res) => {
  try {
    // Assuming you have middleware that sets req.user
    const userId = req.user?.id || req.userId;
    
    if (!userId) {
      return res.status(401).json({ message: 'Unauthorized' });
    }
    
    const user = await User.findById(userId)
      .select('-password')
      .populate('role_id', 'name');
    
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    
    res.json(user);
  } catch (err) {
    console.error('Error fetching current user:', err);
    res.status(500).json({ message: 'Server error' });
  }
};