<?php
session_start();

// Allow access to all users (logged in and guest)
$userRole = $_SESSION['role'] ?? 'guest';
// Consider either email or user_id as a logged-in indicator for compatibility
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <title>Privacy Policy | CORE II Freight Management System</title>
  <style>
    :root {
      --primary-color: #4e73df;
      --primary-gradient: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
      --secondary-gradient: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
      --accent-gradient: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --border-radius: 1rem;
      --shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
      background: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
      color: var(--text-dark);
      line-height: 1.7;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 1.5rem 1rem;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .back-button {
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 1000;
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 50px;
      padding: 0.75rem 1.25rem;
      color: var(--text-dark);
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: var(--shadow);
    }

    .back-button:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-hover);
      color: var(--text-dark);
    }

    .privacy-header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: var(--border-radius);
      padding: 3rem 2rem;
      text-align: center;
      box-shadow: var(--shadow);
      border: 1px solid rgba(255, 255, 255, 0.3);
      position: relative;
      overflow: hidden;
    }

    .privacy-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--secondary-gradient);
    }

    .privacy-header h1 {
      font-size: 2.5rem;
      font-weight: 800;
      background: var(--secondary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .privacy-header h2 {
      color: var(--text-light);
      font-weight: 500;
      margin-bottom: 1rem;
    }

    .effective-date {
      font-style: italic;
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .privacy-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: var(--border-radius);
      padding: 3rem;
      box-shadow: var(--shadow);
      border: 1px solid rgba(255, 255, 255, 0.3);
      flex: 1;
      max-width: 100%;
    }

    .section-title {
      color: var(--text-dark);
      font-weight: 700;
      font-size: 1.3rem;
      margin: 2rem 0 1.25rem 0;
      padding: 1rem 0 1rem 1.25rem;
      background: linear-gradient(90deg, rgba(17, 153, 142, 0.08) 0%, transparent 100%);
      border-left: 4px solid;
      border-image: var(--secondary-gradient) 1;
      border-radius: 0 0.5rem 0.5rem 0;
      position: relative;
      break-inside: avoid;
    }

    .section-title:first-of-type {
      margin-top: 0;
    }

    .subsection {
      margin: 1.25rem 0;
      padding: 1.25rem;
      background: rgba(248, 249, 250, 0.6);
      border-radius: 0.75rem;
      border: 1px solid rgba(0, 0, 0, 0.06);
      break-inside: avoid;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .subsection h4 {
      color: var(--text-dark);
      font-weight: 600;
      font-size: 1.1rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .subsection h4::before {
      content: '';
      width: 8px;
      height: 8px;
      background: var(--accent-gradient);
      border-radius: 50%;
    }

    .highlight-box {
      background: var(--secondary-gradient);
      color: white;
      padding: 1.5rem;
      border-radius: 0.75rem;
      margin: 1.5rem 0;
      box-shadow: 0 8px 25px rgba(17, 153, 142, 0.3);
      position: relative;
      overflow: hidden;
    }

    .info-box {
      background: var(--accent-gradient);
      color: white;
      padding: 1.5rem;
      border-radius: 0.75rem;
      margin: 1.5rem 0;
      box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
      position: relative;
      overflow: hidden;
    }

    .highlight-box::before,
    .info-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
      pointer-events: none;
    }

    .highlight-box h3,
    .info-box h4 {
      margin-bottom: 0.75rem;
      font-weight: 700;
    }

    .data-table {
      background: rgba(17, 153, 142, 0.08);
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin: 1.5rem 0;
      border: 1px solid rgba(17, 153, 142, 0.15);
      box-shadow: 0 2px 8px rgba(17, 153, 142, 0.1);
    }

    .data-table strong {
      color: var(--text-dark);
      font-weight: 700;
    }

    ol, ul {
      padding-left: 1.5rem;
      margin: 1.25rem 0;
    }

    li {
      margin-bottom: 0.8rem;
      line-height: 1.65;
      text-align: justify;
    }

    p {
      margin-bottom: 1.25rem;
      line-height: 1.7;
      text-align: justify;
    }

    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }

    ::-webkit-scrollbar-thumb {
      background: var(--secondary-gradient);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--accent-gradient);
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem 0.5rem;
        gap: 0.75rem;
      }
      
      .privacy-header {
        padding: 2rem 1.5rem;
      }

      .privacy-header h1 {
        font-size: 2rem;
      }
      
      .privacy-content {
        padding: 2rem;
      }

      .section-title {
        font-size: 1.2rem;
        padding: 0.75rem 0 0.75rem 1rem;
        margin: 1.5rem 0 1rem 0;
      }

      .subsection {
        padding: 1rem;
        margin: 1rem 0;
      }

      .back-button {
        top: 10px;
        left: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 0.5rem;
      }

      .privacy-header {
        padding: 1.5rem 1rem;
      }

      .privacy-header h1 {
        font-size: 1.75rem;
      }

      .privacy-content {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/loader.php'; ?>
  <?php if ($isLoggedIn): ?>
  <a href="javascript:history.back()" class="btn btn-primary back-button">
    <i class="bi bi-arrow-left"></i> Back
  </a>
  <?php else: ?>
  <a href="loginpage.php" class="btn btn-primary back-button">
    <i class="bi bi-house"></i> Back to Login
  </a>
  <?php endif; ?>

  <div class="container">
    <div class="privacy-header">
      <h1>Privacy Policy</h1>
      <h2 class="h4 text-muted">CORE II Freight Management System</h2>
      <div class="effective-date">
        Effective Date: <?php echo date('F j, Y'); ?>
      </div>
    </div>

    <div class="privacy-content">
      <div class="highlight-box">
        <h3><i class="bi bi-shield-check"></i> Your Privacy Matters</h3>
        <p>We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, and safeguard your data.</p>
      </div>

      <h3 class="section-title">1. Information We Collect</h3>
      
      <div class="subsection">
        <h4>1.1 Personal Information</h4>
        <div class="data-table">
          <strong>We collect the following personal information:</strong>
          <ul>
            <li>Name, email address, and phone number</li>
            <li>Business/organization information</li>
            <li>Address and location data</li>
            <li>Account credentials and authentication data</li>
            <li>Payment and billing information</li>
          </ul>
        </div>
      </div>

      <div class="subsection">
        <h4>1.2 System Usage Data</h4>
        <ul>
          <li>Login times and session duration</li>
          <li>IP addresses and device information</li>
          <li>Browser type and operating system</li>
          <li>Pages visited and features used</li>
          <li>System performance and error logs</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>1.3 Logistics and Business Data</h4>
        <ul>
          <li>Shipment details and tracking information</li>
          <li>Service provider information and ratings</li>
          <li>Route and scheduling data</li>
          <li>Pricing and tariff information</li>
          <li>Standard Operating Procedures (SOPs)</li>
        </ul>
      </div>

      <h3 class="section-title">2. How We Use Your Information</h3>
      
      <div class="info-box">
        <h4><i class="bi bi-gear"></i> Primary Uses</h4>
        <p>We use your information primarily to provide and improve our logistics management services.</p>
      </div>

      <div class="subsection">
        <h4>2.1 Service Provision</h4>
        <ul>
          <li>Process bookings and manage shipments</li>
          <li>Facilitate communication between users and service providers</li>
          <li>Generate reports and analytics</li>
          <li>Provide customer support and technical assistance</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>2.2 System Security and Improvement</h4>
        <ul>
          <li>Authenticate users and prevent unauthorized access</li>
          <li>Monitor system performance and identify issues</li>
          <li>Analyze usage patterns to improve functionality</li>
          <li>Detect and prevent fraudulent activities</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>2.3 Legal and Compliance</h4>
        <ul>
          <li>Comply with applicable laws and regulations</li>
          <li>Respond to legal requests and court orders</li>
          <li>Maintain records for audit and regulatory purposes</li>
          <li>Protect our rights and interests</li>
        </ul>
      </div>

      <h3 class="section-title">3. Information Sharing and Disclosure</h3>
      
      <div class="highlight-box">
        <h4><i class="bi bi-lock"></i> Limited Sharing</h4>
        <p>We do not sell your personal information. We only share data when necessary for service provision or legal compliance.</p>
      </div>

      <div class="subsection">
        <h4>3.1 Service Providers</h4>
        <ul>
          <li>Shipping and logistics partners for service fulfillment</li>
          <li>Payment processors for billing and transactions</li>
          <li>Cloud hosting providers for data storage</li>
          <li>Technical support vendors for system maintenance</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>3.2 Legal Requirements</h4>
        <ul>
          <li>Government agencies when required by law</li>
          <li>Law enforcement for criminal investigations</li>
          <li>Regulatory bodies for compliance audits</li>
          <li>Courts in response to valid legal orders</li>
        </ul>
      </div>

      <h3 class="section-title">4. Data Security Measures</h3>
      
      <div class="subsection">
        <h4>4.1 Technical Safeguards</h4>
        <ul>
          <li>Encryption of data in transit and at rest</li>
          <li>Secure authentication and access controls</li>
          <li>Regular security audits and vulnerability assessments</li>
          <li>Firewall protection and intrusion detection</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>4.2 Administrative Safeguards</h4>
        <ul>
          <li>Employee training on data protection</li>
          <li>Access controls based on job responsibilities</li>
          <li>Regular review of security policies</li>
          <li>Incident response procedures</li>
        </ul>
      </div>

      <h3 class="section-title">5. Your Rights and Choices</h3>
      
      <div class="info-box">
        <h4><i class="bi bi-person-check"></i> Your Control</h4>
        <p>You have several rights regarding your personal information and how it's used.</p>
      </div>

      <div class="subsection">
        <h4>5.1 Access and Correction</h4>
        <ul>
          <li>Request access to your personal information</li>
          <li>Correct inaccurate or incomplete data</li>
          <li>Update your account information and preferences</li>
          <li>Download your data in a portable format</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>5.2 Data Control</h4>
        <ul>
          <li>Request deletion of your personal information</li>
          <li>Restrict processing of your data</li>
          <li>Object to certain uses of your information</li>
          <li>Withdraw consent where applicable</li>
        </ul>
      </div>

      <h3 class="section-title">6. Data Retention</h3>
      <ul>
        <li>Personal information is retained only as long as necessary</li>
        <li>Account data is kept while your account is active</li>
        <li>Transaction records may be retained for legal and tax purposes</li>
        <li>System logs are typically retained for 12 months</li>
        <li>You can request earlier deletion subject to legal requirements</li>
      </ul>

      <h3 class="section-title">7. International Data Transfers</h3>
      <ul>
        <li>Data may be processed in different countries for service provision</li>
        <li>We ensure appropriate safeguards for international transfers</li>
        <li>Data protection standards are maintained regardless of location</li>
        <li>We comply with applicable cross-border data transfer regulations</li>
      </ul>

      <h3 class="section-title">8. Cookies and Tracking Technologies</h3>
      
      <div class="subsection">
        <h4>8.1 Types of Cookies</h4>
        <ul>
          <li><strong>Essential Cookies:</strong> Required for system functionality</li>
          <li><strong>Performance Cookies:</strong> Help us improve system performance</li>
          <li><strong>Functional Cookies:</strong> Remember your preferences</li>
          <li><strong>Security Cookies:</strong> Protect against unauthorized access</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>8.2 Cookie Management</h4>
        <ul>
          <li>You can control cookies through your browser settings</li>
          <li>Disabling essential cookies may affect system functionality</li>
          <li>We provide cookie preference controls where possible</li>
        </ul>
      </div>

      <h3 class="section-title">9. Children's Privacy</h3>
      <ul>
        <li>Our services are not intended for children under 18</li>
        <li>We do not knowingly collect information from minors</li>
        <li>Parents should supervise children's internet usage</li>
        <li>Contact us if you believe we have collected a child's information</li>
      </ul>

      <h3 class="section-title">10. Changes to This Privacy Policy</h3>
      <ul>
        <li>We may update this Privacy Policy periodically</li>
        <li>Users will be notified of significant changes</li>
        <li>The effective date will be updated with each revision</li>
        <li>Continued use constitutes acceptance of changes</li>
      </ul>

      <h3 class="section-title">11. Contact Us</h3>
      <p>If you have questions about this Privacy Policy or our data practices, please contact us:</p>
      <div class="highlight-box">
        <p><strong>Privacy Officer</strong><br>
        <p><strong>CORE II Freight Management System</strong><br>
        Email: privacycore2@slatefreight.com<br>
        Phone: 0912337812378<br>
        Address: Road 20 Bahay Toro Quezon City</p>
      </div>

      <h3 class="section-title">12. Data Protection Officer</h3>
      <p>For data protection inquiries in jurisdictions that require a Data Protection Officer:</p>
      <div class="info-box">
        <p><strong>Data Protection Officer</strong><br>
        Email: dpocore2@slatefreight.com<br>
        Phone: 0913783722333</p>
      </div>

      <div class="text-center mt-4 pt-4 border-top">
        <p class="text-muted">
          <small>Last updated: <?php echo date('F j, Y'); ?> | Version 1.0</small>
        </p>
        <p class="mt-2">
          <a href="terms.php" class="btn btn-outline-primary btn-sm me-2">
            <i class="bi bi-file-text"></i> Terms & Conditions
          </a>
          <?php if ($isLoggedIn): ?>
          <a href="user-profile.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-person-gear"></i> Privacy Settings
          </a>
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
