require('dotenv').config();
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');

const app = express();

const authRoutes = require('./routes/auth');
const usersRouter = require('./routes/Users');
const RolesRouter = require('./routes/Roles');
const EventsRouter = require('./routes/Events');
const CategoryRouter = require('./routes/category');
const memberEventRoutes = require('./routes/Member');
const registrationRoutes = require('./routes/Registration');
const paymentRoutes = require('./routes/Payment');
const attendanceRoutes = require('./routes/Attendance');
const certificateRoutes = require('./routes/Certificate');
const dashboardRoutes = require('./routes/Dashboard');

// CORS
app.use(cors({
  origin: 'http://localhost:8000',
  credentials: true,
  methods: ['GET', 'POST', 'OPTIONS'],
  allowedHeaders: ['Content-Type', 'Authorization']
}));

app.use(express.json());

// Connect MongoDB
mongoose.connect(process.env.MONGODB_URI || 'mongodb://localhost:27017/auth_demo', {
  useNewUrlParser: true,
  useUnifiedTopology: true
});


// Routes
app.use('/api', authRoutes);
app.use('/api/users', usersRouter);
app.use('/api/role', RolesRouter);
app.use('/api/events', EventsRouter);
app.use('/api/category', CategoryRouter);
app.use('/api/member/events', memberEventRoutes);
app.use('/api/registrations', registrationRoutes);
app.use('/api/payments', paymentRoutes);
app.use('/api/attendance', attendanceRoutes);
app.use('/api/certificate', certificateRoutes);
app.use('/api/dashboard', dashboardRoutes);

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
