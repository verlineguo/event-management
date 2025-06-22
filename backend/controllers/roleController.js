const Role = require('../models/Role');

exports.getAllRoles = async (req, res) => {
  try {
    const roles = await Role.find().select('_id name status');
    res.json(roles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// Get event role by ID
exports.getRoleById = async (req, res) => {
  try {
    const role = await Role.findById(req.params.id);
    if (!role) return res.status(404).json({ message: 'Role not found' });
    res.json(role);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Create a new event role
exports.createRole = async (req, res) => {
  try {
    const { name } = req.body;

    // Check for existing role with the same name (optional)
    const existingRole = await Role.findOne({ name });
    if (existingRole) {
      return res.status(400).json({ message: 'Role already exists' });
    }

    const role = new Role({
          name: req.body.name,
          status: req.body.status
        });
    const newRole = await role.save();
    res.status(201).json(newRole);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Update an event role
exports.updateRole = async (req, res) => {
  try {
    const role = await Role.findById(req.params.id);
    if (!role) return res.status(404).json({ message: 'Role not found' });

    if (req.body.name) role.name = req.body.name;
    if (req.body.status !== undefined) role.status = req.body.status;

    const updatedRole = await role.save();
    res.json(updatedRole);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Delete an event role
exports.deleteRole = async (req, res) => {
  try {
    const role = await Role.findById(req.params.id);
    if (!role) return res.status(404).json({ message: 'Role not found' });

    await Role.deleteOne({ _id: req.params.id });
    res.json({ message: 'Role deleted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
