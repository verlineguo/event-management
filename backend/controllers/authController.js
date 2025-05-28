const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const User = require('../models/User');
const Role = require('../models/Role');

exports.register = async (req, res) => {
  try {
    const memberRole = await Role.findOne({ name: 'member' });
    if (!memberRole) {
      return res.status(500).json({ error: 'Default member role not found in the database' });
    }

    const hashedPassword = await bcrypt.hash(req.body.password, 10);
    const user = new User({
      name: req.body.name,
      email: req.body.email,
      password: hashedPassword,
      role_id: memberRole._id,
      status: true,
    });

    await user.save();
    res.status(201).json({ message: 'User created' });
  } catch (err) {
    res.status(500).json({ error: 'Error registering user' });
  }
};

exports.login = async (req, res) => {
  try {
    const user = await User.findOne({ email: req.body.email }).populate('role_id');
    if (!user) return res.status(400).json({ error: 'User not found' });

    if (user.status !== true) {
      return res.status(403).json({ error: 'Account is inactive or suspended' });
    }

    const validPass = await bcrypt.compare(req.body.password, user.password);
    if (!validPass) return res.status(400).json({ error: 'Invalid password' });

    const token = jwt.sign({
      _id: user._id,
      role_id: user.role_id
    }, process.env.JWT_SECRET || 'your-fallback-secret-key', { expiresIn: '1h' });

    return res.json({
      token,
      user: {
        _id: user._id,
        email: user.email,
        role_id: user.role_id,
        role_name: user.role_id.name,
        status: user.status
      }
    });
  } catch (err) {
    console.error("Login error:", err);
    return res.status(500).json({ error: 'Server error during authentication' });
  }
};

exports.getUserRole = (req, res) => {
  res.json({ role_id: req.user.role_id });
};

exports.verifyToken = (req, res) => {
  res.json({ valid: true });
};

exports.getProfile = async (req, res) => {
  const user = await User.findById(req.user._id);
  res.json({ email: user.email });
};
