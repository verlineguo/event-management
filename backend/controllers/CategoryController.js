const EventCategory = require('../models/eventCategory');

// Get all event categories
exports.getAllCategories = async (req, res) => {
  try {
    const categories = await EventCategory.find();
    res.json(categories);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Get event category by ID
exports.getCategoryById = async (req, res) => {
  try {
    const category = await EventCategory.findById(req.params.id);
    if (!category) return res.status(404).json({ message: 'Category not found' });
    res.json(category);
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};

// Create a new event category
exports.createCategory = async (req, res) => {
  try {
    const { name } = req.body;

    // Check for existing category with the same name (optional)
    const existingCategory = await EventCategory.findOne({ name });
    if (existingCategory) {
      return res.status(400).json({ message: 'Category already exists' });
    }

    const category = new EventCategory({
          name: req.body.name,
          status: req.body.status
        });
    const newCategory = await category.save();
    res.status(201).json(newCategory);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Update an event category
exports.updateCategory = async (req, res) => {
  try {
    const category = await EventCategory.findById(req.params.id);
    if (!category) return res.status(404).json({ message: 'Category not found' });

    if (req.body.name) category.name = req.body.name;
    if (req.body.status !== undefined) category.status = req.body.status;

    const updatedCategory = await category.save();
    res.json(updatedCategory);
  } catch (err) {
    res.status(400).json({ message: err.message });
  }
};

// Delete an event category
exports.deleteCategory = async (req, res) => {
  try {
    const category = await EventCategory.findById(req.params.id);
    if (!category) return res.status(404).json({ message: 'Category not found' });

    await EventCategory.deleteOne({ _id: req.params.id });
    res.json({ message: 'Category deleted' });
  } catch (err) {
    res.status(500).json({ message: err.message });
  }
};
