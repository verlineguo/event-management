const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
    email: { type: String, required: true, unique: true },
    password: { type: String, required: true },
    name: { type: String, required: true },
    gender: { type: String, enum: ['male', 'female'] },
    phone: String,
    profile: String,
    status: Boolean,
    role_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Role' }
}, { timestamps: true });

module.exports = mongoose.model('User', userSchema);
