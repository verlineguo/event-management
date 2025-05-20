const mongoose = require('mongoose');

const roleSchema = new mongoose.Schema({
  name: { 
    type: String,    
    required: true,
    enum: ['guest', 'member', 'admin', 'finance', 'committee'] 
  },
  
},

 { timestamps: true });

module.exports = mongoose.model('Role', roleSchema);
