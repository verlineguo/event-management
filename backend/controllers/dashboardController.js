const User = require('../models/User');
const Role = require('../models/Role');
const Event = require('../models/Event');
const mongoose = require('mongoose');

/**
 * @desc
 * @route
 * @access
 */
exports.getDashboardStats = async (req, res) => {
    try {
        const memberRole = await Role.findOne({ name: 'member' });
        if (!memberRole) {
            return res.status(404).json({ message: 'Role "member" not found in the database. Please ensure it exists in your Roles collection.' });
        }

        const committeeRole = await Role.findOne({ name: 'committee' });
        if (!committeeRole) {
            return res.status(404).json({ message: 'Role "committee" not found in the database. Please ensure it exists in your Roles collection.' });
        }

        const memberCount = await User.countDocuments({ role_id: memberRole._id });
        const committeeCount = await User.countDocuments({ role_id: committeeRole._id });
        const totalEvents = await Event.countDocuments();

        res.json({
            memberCount: memberCount,
            committeeCount: committeeCount,
            totalEvents: totalEvents,
            message: 'Dashboard statistics fetched successfully.'
        });

    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
        res.status(500).json({
            message: 'An error occurred while fetching dashboard data.',
            error: error.message
        });
    }
};