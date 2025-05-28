const Role = require('../models/Role');

exports.getAllRoles = async (req, res) => {
  try {
    const roles = await Role.find().select('_id name');
    res.json(roles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};
