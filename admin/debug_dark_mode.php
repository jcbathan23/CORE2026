<?php
require '../connect.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dark Mode Debug</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      transition: all 0.3s ease;
      padding: 20px;
    }
    
    /* Light mode styles */
    body {
      background: #ffffff;
      color: #000000;
    }
    
    /* Dark mode styles */
    body.dark-mode {
      background: linear-gradient(180deg, #2b3f4e 0%, #1f3442 100%) !important;
      color: #e6edf3 !important;
    }
    
    .test-card {
      background: #ffffff;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      margin: 10px 0;
      transition: all 0.3s ease;
    }
    
    body.dark-mode .test-card {
      background: rgba(31, 52, 66, 0.95) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #e6edf3 !important;
    }
    
    .debug-info {
      font-family: monospace;
      background: #f8f9fa;
      padding: 10px;
      border-radius: 4px;
      margin: 10px 0;
    }
    
    body.dark-mode .debug-info {
      background: rgba(255, 255, 255, 0.1);
      color: #e6edf3;
    }
    
    .toggle-btn {
      background: #007bff;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      margin: 10px 5px;
    }
    
    .toggle-btn:hover {
      background: #0056b3;
    }
    
    body.dark-mode .toggle-btn {
      background: #4f46e5;
    }
    
    body.dark-mode .toggle-btn:hover {
      background: #3730a3;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Dark Mode Debug Page</h1>
    
    <div class="test-card">
      <h3>Dark Mode Controls</h3>
      <button class="toggle-btn" onclick="toggleDarkMode()">
        <i class="fas fa-moon" id="toggleIcon"></i>
        <span id="toggleText">Enable Dark Mode</span>
      </button>
      <button class="toggle-btn" onclick="clearStorage()">Clear Storage</button>
      <button class="toggle-btn" onclick="refreshDebugInfo()">Refresh Info</button>
    </div>
    
    <div class="test-card">
      <h3>Debug Information</h3>
      <div class="debug-info" id="debugInfo">
        Loading debug info...
      </div>
    </div>
    
    <div class="test-card">
      <h3>Test Elements</h3>
      <p>This is a paragraph of text that should change color in dark mode.</p>
      <div class="alert alert-primary">This is a primary alert</div>
      <div class="alert alert-success">This is a success alert</div>
      <button class="btn btn-primary">Primary Button</button>
      <button class="btn btn-secondary">Secondary Button</button>
    </div>
    
    <div class="test-card">
      <h3>Form Elements</h3>
      <div class="mb-3">
        <label for="testInput" class="form-label">Test Input</label>
        <input type="text" class="form-control" id="testInput" placeholder="Type something...">
      </div>
      <div class="mb-3">
        <label for="testSelect" class="form-label">Test Select</label>
        <select class="form-select" id="testSelect">
          <option>Option 1</option>
          <option>Option 2</option>
          <option>Option 3</option>
        </select>
      </div>
    </div>
  </div>

  <script>
    function updateDebugInfo() {
      const debugInfo = document.getElementById('debugInfo');
      const isDark = document.body.classList.contains('dark-mode');
      const storageValue = localStorage.getItem('dark-mode');
      
      debugInfo.innerHTML = `
        <strong>Current State:</strong><br>
        Body has 'dark-mode' class: ${isDark}<br>
        HTML has 'dark-mode' class: ${document.documentElement.classList.contains('dark-mode')}<br>
        localStorage value: ${storageValue}<br>
        Computed background color: ${getComputedStyle(document.body).backgroundColor}<br>
        Computed color: ${getComputedStyle(document.body).color}<br>
        <br>
        <strong>Body Classes:</strong> ${document.body.className}<br>
        <strong>HTML Classes:</strong> ${document.documentElement.className}<br>
      `;
    }
    
    function updateToggleButton() {
      const icon = document.getElementById('toggleIcon');
      const text = document.getElementById('toggleText');
      const isDark = document.body.classList.contains('dark-mode');
      
      if (isDark) {
        icon.className = 'fas fa-sun';
        text.textContent = 'Disable Dark Mode';
      } else {
        icon.className = 'fas fa-moon';
        text.textContent = 'Enable Dark Mode';
      }
    }
    
    function toggleDarkMode() {
      const isDark = document.body.classList.contains('dark-mode');
      console.log('Toggling dark mode. Current state:', isDark);
      
      if (isDark) {
        document.body.classList.remove('dark-mode');
        document.documentElement.classList.remove('dark-mode');
        localStorage.setItem('dark-mode', 'disabled');
      } else {
        document.body.classList.add('dark-mode');
        document.documentElement.classList.add('dark-mode');
        localStorage.setItem('dark-mode', 'enabled');
      }
      
      updateToggleButton();
      updateDebugInfo();
    }
    
    function clearStorage() {
      localStorage.removeItem('dark-mode');
      document.body.classList.remove('dark-mode');
      document.documentElement.classList.remove('dark-mode');
      updateToggleButton();
      updateDebugInfo();
    }
    
    function refreshDebugInfo() {
      updateDebugInfo();
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Check localStorage and apply dark mode if enabled
      const darkModeEnabled = localStorage.getItem('dark-mode') === 'enabled';
      if (darkModeEnabled) {
        document.body.classList.add('dark-mode');
        document.documentElement.classList.add('dark-mode');
      }
      
      updateToggleButton();
      updateDebugInfo();
      
      // Update debug info every second to catch any changes
      setInterval(updateDebugInfo, 1000);
    });
  </script>
</body>
</html>
