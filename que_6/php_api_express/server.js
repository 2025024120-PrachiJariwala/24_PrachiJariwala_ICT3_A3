const express = require('express');

const app = express();
const PORT = 3001;

// API URL provided by PHP
const PHP_API_URL =
    'http://localhost/php_assignment/24_PrachiJariwala_ICT3_A3/que_6/php_api_express/students.php';

// Route to call PHP REST API
app.get('/api/students', async (req, res) => {
    try {
        const response = await fetch(PHP_API_URL);

        if (!response.ok) {
            return res.status(response.status).json({
                success: false,
                message: 'PHP API request failed'
            });
        }

        const result = await response.json();

        res.json(result);
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Unable to connect to PHP API'
        });
    }
});

// Start Express server
app.listen(PORT, () => {
    console.log(`Express server running at http://localhost:${PORT}`);
});