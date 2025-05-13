require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const cors = require('cors');

const app = express();

// Improved CORS configuration
app.use(cors({
  origin: 'http://localhost:8000', // Laravel frontend URL
  credentials: true,
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));

app.use(express.json());

// Koneksi MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/auth_demo', {
  useNewUrlParser: true,
  useUnifiedTopology: true
});

// Model User
const userSchema = new mongoose.Schema({
   email: { type: String, unique: true },
   password: String,
   role_id: { type: Number} 
});
const User = mongoose.model('User', userSchema);

// Middleware Auth
const authenticateToken = (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];
  
  if (!token) return res.sendStatus(401);

  jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
    if (err) return res.sendStatus(403);

    req.user = user;
    next();
  });
};

// Routes
app.post('/api/register', async (req, res) => {
  try {
    const hashedPassword = await bcrypt.hash(req.body.password, 10);
    const user = new User({
      email: req.body.email,
      password: hashedPassword,
      role_id: req.body.role_id
    });
    await user.save();
    res.status(201).json({ message: 'User created' });
  } catch (err) {
    res.status(500).json({ error: 'Error registering user' });
  }
});

app.post('/api/login', async (req, res) => {
  try {
    const user = await User.findOne({ email: req.body.email });
    if (!user) return res.status(400).json({ error: 'User not found' });
    
    const validPass = await bcrypt.compare(req.body.password, user.password);
    if (!validPass) return res.status(400).json({ error: 'Invalid password' });
    
    const token = jwt.sign({ 
      _id: user._id,
      role_id: user.role_id || 4  // Provide default role if missing
    }, process.env.JWT_SECRET || 'your-fallback-secret-key', { expiresIn: '1h' });
    
    
    return res.json({
      token,
      user: {
        email: user.email,
        role_id: user.role_id || 4
      }
    });
  } catch (err) {
    console.error("Login error:", err);
    return res.status(500).json({ error: 'Server error during authentication' });
  }
});

app.get('/api/user-role', authenticateToken, async (req, res) => {
  res.json({ role_id: req.user.role_id });
});

app.get('/api/verify-token', authenticateToken, (req, res) => {
  res.json({ valid: true });
});

app.get('/api/profile', authenticateToken, async (req, res) => {
  const user = await User.findById(req.user._id);
  res.json({ email: user.email });
});

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => console.log(`Node.js auth API running on port ${PORT}`));