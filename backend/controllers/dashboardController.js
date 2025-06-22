const User = require('../models/User');
const Role = require('../models/Role');
const EventCategory = require('../models/eventCategory');
const Event = require('../models/Event');
const Session = require('../models/Session');
const Registration = require('../models/Registration');
const Attendance = require('../models/Attendance');
const Certificate = require('../models/Certificate');

exports.getAdminDashboardStats = async (req, res) => {
  try {
    // Total Users
    const totalUsers = await User.countDocuments();
    
    // Active Users
    const activeUsers = await User.countDocuments({ status: true });
    
    // Inactive Users
    const inactiveUsers = await User.countDocuments({ status: false });
    
    // Total Roles
    const totalRoles = await Role.countDocuments();
    
    // Active Roles
    const activeRoles = await Role.countDocuments({ status: true });
    
    // Total Categories
    const totalCategories = await EventCategory.countDocuments();
    
    // Active Categories
    const activeCategories = await EventCategory.countDocuments({ status: true });

    // User Registration Growth (Last 6 months)
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    
    const monthlyUserRegistrations = await User.aggregate([
      {
        $match: {
          createdAt: { $gte: sixMonthsAgo }
        }
      },
      {
        $group: {
          _id: {
            year: { $year: "$createdAt" },
            month: { $month: "$createdAt" }
          },
          count: { $sum: 1 }
        }
      },
      {
        $sort: { "_id.year": 1, "_id.month": 1 }
      }
    ]);

    // Users by Role Distribution
    const usersByRole = await User.aggregate([
      {
        $lookup: {
          from: 'roles',
          localField: 'role_id',
          foreignField: '_id',
          as: 'role'
        }
      },
      {
        $unwind: {
          path: '$role',
          preserveNullAndEmptyArrays: true
        }
      },
      {
        $group: {
          _id: '$role.name',
          count: { $sum: 1 }
        }
      },
      {
        $sort: { count: -1 }
      }
    ]);

    // Recent Users (Last 7 days)
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
    
    const recentUsers = await User.find({
      createdAt: { $gte: sevenDaysAgo }
    })
    .populate('role_id', 'name')
    .sort({ createdAt: -1 })
    .limit(5)
    .select('name email status createdAt role_id');

    // Calculate growth percentage for users
    const currentMonth = new Date();
    const previousMonth = new Date();
    previousMonth.setMonth(previousMonth.getMonth() - 1);
    
    const currentMonthUsers = await User.countDocuments({
      createdAt: {
        $gte: new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1),
        $lt: new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1)
      }
    });
    
    const previousMonthUsers = await User.countDocuments({
      createdAt: {
        $gte: new Date(previousMonth.getFullYear(), previousMonth.getMonth(), 1),
        $lt: new Date(previousMonth.getFullYear(), previousMonth.getMonth() + 1, 1)
      }
    });
    
    const userGrowthPercentage = previousMonthUsers > 0 
      ? ((currentMonthUsers - previousMonthUsers) / previousMonthUsers * 100).toFixed(2)
      : 0;

    // User Status Distribution for Chart
    const userStatusStats = [
      { name: 'Active', value: activeUsers },
      { name: 'Inactive', value: inactiveUsers }
    ];

    const response = {
      stats: {
        totalUsers,
        activeUsers,
        inactiveUsers,
        totalRoles,
        activeRoles,
        totalCategories,
        activeCategories,
        userGrowthPercentage: parseFloat(userGrowthPercentage)
      },
      charts: {
        monthlyUserRegistrations,
        usersByRole,
        userStatusStats
      },
      recentActivity: {
        recentUsers
      }
    };

    res.json(response);
  } catch (error) {
    console.error('Error fetching admin dashboard stats:', error);
    res.status(500).json({ message: error.message });
  }
};



exports.getCommitteeDashboardStats = async (req, res) => {
  try {

    // Semua Events (bukan hanya yang dibuat oleh committee ini)
    const allEvents = await Event.find({});
    const allEventIds = allEvents.map(event => event._id);

    // Total Events
    const totalEvents = await Event.countDocuments({});
    
    // Events by Status
    const openEvents = await Event.countDocuments({ status: 'open' });
    const closedEvents = await Event.countDocuments({ status: 'closed' });
    const completedEvents = await Event.countDocuments({ status: 'completed' });
    const cancelledEvents = await Event.countDocuments({ status: 'cancelled' });

    // Total Sessions untuk semua events
    const totalSessions = await Session.countDocuments({ event_id: { $in: allEventIds } });
    
    // Sessions by Status
    const scheduledSessions = await Session.countDocuments({ 
      event_id: { $in: allEventIds }, 
      status: 'scheduled' 
    });
    const ongoingSessions = await Session.countDocuments({ 
      event_id: { $in: allEventIds }, 
      status: 'ongoing' 
    });
    const completedSessions = await Session.countDocuments({ 
      event_id: { $in: allEventIds }, 
      status: 'completed' 
    });

    // Total Registrations untuk semua events
    const totalRegistrations = await Registration.countDocuments({ 
      event_id: { $in: allEventIds } 
    });
    
    // Registrations by Payment Status
    const pendingPayments = await Registration.countDocuments({ 
      event_id: { $in: allEventIds }, 
      payment_status: 'pending' 
    });
    const approvedPayments = await Registration.countDocuments({ 
      event_id: { $in: allEventIds }, 
      payment_status: 'approved' 
    });

    // Total Attendance
    const totalAttendance = await Attendance.countDocuments({ 
      session_id: { $in: await Session.find({ event_id: { $in: allEventIds } }).distinct('_id') }
    });

    // Total Certificates Issued
    const totalCertificates = await Certificate.countDocuments({ 
      session_id: { $in: await Session.find({ event_id: { $in: allEventIds } }).distinct('_id') }
    });

    // Event Registration Growth (Last 6 months)
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    
    const monthlyRegistrations = await Registration.aggregate([
      {
        $match: {
          event_id: { $in: allEventIds },
          createdAt: { $gte: sixMonthsAgo }
        }
      },
      {
        $group: {
          _id: {
            year: { $year: "$createdAt" },
            month: { $month: "$createdAt" }
          },
          count: { $sum: 1 }
        }
      },
      {
        $sort: { "_id.year": 1, "_id.month": 1 }
      }
    ]);

    // Events by Category
    const eventsByCategory = await Event.aggregate([
      {
        $lookup: {
          from: 'eventcategories',
          localField: 'category_id',
          foreignField: '_id',
          as: 'category'
        }
      },
      {
        $unwind: '$category'
      },
      {
        $group: {
          _id: '$category.name',
          count: { $sum: 1 }
        }
      },
      {
        $sort: { count: -1 }
      }
    ]);

    // Recent Events (Last 5)
    const recentEvents = await Event.find({})
      .populate('category_id', 'name')
      .populate('created_by', 'name')
      .sort({ createdAt: -1 })
      .limit(5)
      .select('name status max_participants createdAt category_id created_by');

    // Upcoming Sessions (Next 7 days)
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 7);
    
    const upcomingSessions = await Session.find({
      event_id: { $in: allEventIds },
      date: { 
        $gte: new Date(),
        $lte: nextWeek 
      },
      status: { $in: ['scheduled', 'ongoing'] }
    })
    .populate('event_id', 'name')
    .sort({ date: 1, start_time: 1 })
    .limit(10);

    // Recent Attendance Scans (Last 20)
    const recentAttendance = await Attendance.find({
      session_id: { $in: await Session.find({ event_id: { $in: allEventIds } }).distinct('_id') }
    })
    .populate({
      path: 'session_id',
      select: 'title event_id',
      populate: {
        path: 'event_id',
        select: 'name'
      }
    })
    .populate('user_id', 'name email')
    .sort({ createdAt: -1 })
    .limit(20);

    // Events Performance Stats
    const eventsPerformance = await Event.aggregate([
      {
        $lookup: {
          from: 'registrations',
          localField: '_id',
          foreignField: 'event_id',
          as: 'registrations'
        }
      },
      {
        $addFields: {
          registrationCount: { $size: '$registrations' },
          occupancyRate: {
            $multiply: [
              { $divide: [{ $size: '$registrations' }, '$max_participants'] },
              100
            ]
          }
        }
      },
      {
        $project: {
          name: 1,
          status: 1,
          max_participants: 1,
          registrationCount: 1,
          occupancyRate: { $round: ['$occupancyRate', 1] }
        }
      },
      {
        $sort: { occupancyRate: -1 }
      },
      {
        $limit: 5
      }
    ]);

    // Registration Status Distribution
    const registrationStatusStats = await Registration.aggregate([
      {
        $match: { event_id: { $in: allEventIds } }
      },
      {
        $group: {
          _id: '$payment_status',
          count: { $sum: 1 }
        }
      }
    ]);

    // Calculate growth percentage for events
    const currentMonth = new Date();
    const previousMonth = new Date();
    previousMonth.setMonth(previousMonth.getMonth() - 1);
    
    const currentMonthEvents = await Event.countDocuments({
      createdAt: {
        $gte: new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1),
        $lt: new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1)
      }
    });
    
    const previousMonthEvents = await Event.countDocuments({
      createdAt: {
        $gte: new Date(previousMonth.getFullYear(), previousMonth.getMonth(), 1),
        $lt: new Date(previousMonth.getFullYear(), previousMonth.getMonth() + 1, 1)
      }
    });
    
    const eventGrowthPercentage = previousMonthEvents > 0 
      ? ((currentMonthEvents - previousMonthEvents) / previousMonthEvents * 100).toFixed(2)
      : 0;

    const response = {
      stats: {
        totalEvents,
        openEvents,
        closedEvents,
        completedEvents,
        cancelledEvents,
        totalSessions,
        scheduledSessions,
        ongoingSessions,
        completedSessions,
        totalRegistrations,
        pendingPayments,
        approvedPayments,
        totalAttendance,
        totalCertificates,
        eventGrowthPercentage: parseFloat(eventGrowthPercentage)
      },
      charts: {
        monthlyRegistrations,
        eventsByCategory,
        registrationStatusStats,
        eventsPerformance
      },
      recentActivity: {
        recentEvents,
        upcomingSessions,
        recentAttendance
      }
    };

    res.json(response);
  } catch (error) {
    console.error('Error fetching committee dashboard stats:', error);
    res.status(500).json({ message: error.message });
  
}
};



exports.getFinanceDashboardStats = async (req, res) => {
  try {
    // Total Registrations
    const totalRegistrations = await Registration.countDocuments();
    
    // Payment Status Statistics
    const pendingPayments = await Registration.countDocuments({ payment_status: 'pending' });
    const approvedPayments = await Registration.countDocuments({ payment_status: 'approved' });
    const rejectedPayments = await Registration.countDocuments({ payment_status: 'rejected' });
    
    // Today's Payment Activities
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const todayPendingPayments = await Registration.countDocuments({
      payment_status: 'pending',
      createdAt: { $gte: today, $lt: tomorrow }
    });
    
    const todayProcessedPayments = await Registration.countDocuments({
      payment_status: { $in: ['approved', 'rejected'] },
      payment_verified_at: { $gte: today, $lt: tomorrow }
    });

    // This Week's Payment Activities
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    
    const weeklyPendingPayments = await Registration.countDocuments({
      payment_status: 'pending',
      createdAt: { $gte: weekAgo }
    });
    
    const weeklyProcessedPayments = await Registration.countDocuments({
      payment_status: { $in: ['approved', 'rejected'] },
      payment_verified_at: { $gte: weekAgo }
    });

    // Payment Revenue Statistics
    const totalRevenue = await Registration.aggregate([
      {
        $match: { payment_status: 'approved' }
      },
      {
        $group: {
          _id: null,
          total: { $sum: '$payment_amount' }
        }
      }
    ]);

    const monthlyRevenue = await Registration.aggregate([
      {
        $match: { 
          payment_status: 'approved',
          payment_verified_at: { $gte: new Date(new Date().getFullYear(), new Date().getMonth(), 1) }
        }
      },
      {
        $group: {
          _id: null,
          total: { $sum: '$payment_amount' }
        }
      }
    ]);

    // Daily Payment Verification Trend (Last 7 days)
    const dailyPaymentStats = await Registration.aggregate([
      {
        $match: {
          payment_status: { $in: ['approved', 'rejected'] },
          payment_verified_at: { $gte: weekAgo }
        }
      },
      {
        $group: {
          _id: {
            date: { 
              $dateToString: { 
                format: "%Y-%m-%d", 
                date: "$payment_verified_at" 
              } 
            },
            status: '$payment_status'
          },
          count: { $sum: 1 }
        }
      },
      {
        $sort: { "_id.date": 1 }
      }
    ]);

    // Monthly Payment Trend (Last 6 months)
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);
    
    const monthlyPaymentStats = await Registration.aggregate([
      {
        $match: {
          createdAt: { $gte: sixMonthsAgo }
        }
      },
      {
        $group: {
          _id: {
            year: { $year: "$createdAt" },
            month: { $month: "$createdAt" },
            status: '$payment_status'
          },
          count: { $sum: 1 },
          amount: { $sum: '$payment_amount' }
        }
      },
      {
        $sort: { "_id.year": 1, "_id.month": 1 }
      }
    ]);

    // Payment Status Distribution for Chart
    const paymentStatusStats = [
      { name: 'Pending', value: pendingPayments, color: '#ffc107' },
      { name: 'Approved', value: approvedPayments, color: '#28a745' },
      { name: 'Rejected', value: rejectedPayments, color: '#dc3545' }
    ];

    // Recent Payment Activities (Last 20)
    const recentPayments = await Registration.find({
      payment_status: { $in: ['pending', 'approved', 'rejected'] }
    })
    .populate('event_id', 'name')
    .populate('user_id', 'name email')
    .populate('payment_verified_by', 'name')
    .sort({ createdAt: -1 })
    .limit(20)
    .select('payment_amount payment_status payment_verified_at payment_verified_by rejection_reason createdAt event_id user_id');

    // Pending Payments (Most Urgent - Oldest First)
    const urgentPendingPayments = await Registration.find({
      payment_status: 'pending',
      payment_proof_url: { $exists: true, $ne: null }
    })
    .populate('event_id', 'name')
    .populate('user_id', 'name email phone')
    .sort({ createdAt: 1 })
    .limit(10)
    .select('payment_amount createdAt event_id user_id payment_proof_url');

    // Events with Most Pending Payments
    const eventsPendingPayments = await Registration.aggregate([
      {
        $match: { payment_status: 'pending' }
      },
      {
        $lookup: {
          from: 'events',
          localField: 'event_id',
          foreignField: '_id',
          as: 'event'
        }
      },
      {
        $unwind: '$event'
      },
      {
        $group: {
          _id: '$event_id',
          eventName: { $first: '$event.name' },
          pendingCount: { $sum: 1 },
          totalAmount: { $sum: '$payment_amount' }
        }
      },
      {
        $sort: { pendingCount: -1 }
      },
      {
        $limit: 5
      }
    ]);

    // Calculate growth percentage for payments
    const currentMonth = new Date();
    const previousMonth = new Date();
    previousMonth.setMonth(previousMonth.getMonth() - 1);
    
    const currentMonthPayments = await Registration.countDocuments({
      payment_status: 'approved',
      payment_verified_at: {
        $gte: new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1),
        $lt: new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1)
      }
    });
    
    const previousMonthPayments = await Registration.countDocuments({
      payment_status: 'approved',
      payment_verified_at: {
        $gte: new Date(previousMonth.getFullYear(), previousMonth.getMonth(), 1),
        $lt: new Date(previousMonth.getFullYear(), previousMonth.getMonth() + 1, 1)
      }
    });
    
    const paymentGrowthPercentage = previousMonthPayments > 0 
      ? ((currentMonthPayments - previousMonthPayments) / previousMonthPayments * 100).toFixed(2)
      : 0;

    const response = {
      stats: {
        totalRegistrations,
        pendingPayments,
        approvedPayments,
        rejectedPayments,
        todayPendingPayments,
        todayProcessedPayments,
        weeklyPendingPayments,
        weeklyProcessedPayments,
        totalRevenue: totalRevenue[0]?.total || 0,
        monthlyRevenue: monthlyRevenue[0]?.total || 0,
        paymentGrowthPercentage: parseFloat(paymentGrowthPercentage)
      },
      charts: {
        dailyPaymentStats,
        monthlyPaymentStats,
        paymentStatusStats,
        eventsPendingPayments
      },
      recentActivity: {
        recentPayments,
        urgentPendingPayments
      }
    };

    res.json(response);
  } catch (error) {
    console.error('Error fetching finance dashboard stats:', error);
    res.status(500).json({ message: error.message });
  }
};