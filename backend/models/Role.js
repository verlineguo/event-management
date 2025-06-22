const mongoose = require('mongoose');

const roleSchema = new mongoose.Schema({
  name: { 
    type: String,    
    required: true,
  },
  status: { type: Boolean, required: true }

  
},

 { timestamps: true });

module.exports = mongoose.model('Role', roleSchema);
