const express = require('express');

const app = express();
const PORT = 3001;

app.use(express.json());

// Sample student data
const students = [
    { id: 1, name: 'Amit Patel', city: 'Surat' },
    { id: 2, name: 'Neha Shah', city: 'Ahmedabad' },
    { id: 3, name: 'Rahul Mehta', city: 'Vadodara' }
];

// API to get all students
app.get('/api/students', (req, res) => {
    res.json({
        success: true,
        data: students
    });
});

// API to get a student by ID
app.get('/api/students/:id', (req, res) => {
    const id = Number(req.params.id);

    const student = students.find(s => s.id === id);

    if (!student) {
        return res.status(404).json({
            success: false,
            message: 'Student not found'
        });
    }

    res.json({
        success: true,
        data: student
    });
});

// Start Express server
app.listen(PORT, () => {
    console.log(`Express API running at http://localhost:${PORT}`);
});