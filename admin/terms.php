<?php
  // Start session and determine login status for conditional back button
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
  $isLoggedIn = isset($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <title>Terms and Conditions | CORE II Freight Management System</title>
  <style>
    :root {
      --primary-color: #4e73df;
      --primary-gradient: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
      --secondary-gradient: linear-gradient(135deg, #0b2530 0%, #1f3541 100%);
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

    .terms-header {
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

    .terms-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: var(--primary-gradient);
    }

    .terms-header h1 {
      font-size: 2.5rem;
      font-weight: 800;
      background: var(--primary-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .terms-header h2 {
      color: var(--text-light);
      font-weight: 500;
      margin-bottom: 1rem;
    }

    .effective-date {
      font-style: italic;
      color: var(--text-light);
      font-size: 0.9rem;
    }

    .terms-content {
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
      background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
      border-left: 4px solid;
      border-image: var(--primary-gradient) 1;
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
      background: var(--secondary-gradient);
      border-radius: 50%;
    }

    .highlight-box {
      background: var(--primary-gradient);
      color: white;
      padding: 1.5rem;
      border-radius: 0.75rem;
      margin: 1.5rem 0;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      position: relative;
      overflow: hidden;
    }

    .highlight-box::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
      pointer-events: none;
    }

    .highlight-box h3 {
      margin-bottom: 0.75rem;
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
      background: var(--primary-gradient);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: var(--secondary-gradient);
    }

    @media (max-width: 768px) {
      .container {
        padding: 1rem 0.5rem;
        gap: 0.75rem;
      }
      
      .terms-header {
        padding: 2rem 1.5rem;
      }

      .terms-header h1 {
        font-size: 2rem;
      }
      
      .terms-content {
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

      .terms-header {
        padding: 1rem 0.5rem;
      }

      .terms-header h1 {
        font-size: 1.5rem;
      }

      .terms-content {
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
    <i class="bi bi-house"></i> Home
  </a>
  <?php endif; ?>

  <div class="container">
    <div class="terms-header">
      <h1>Terms and Conditions</h1>
      <h2 class="h4 text-muted">CORE II Freight Management System</h2>
      <div class="effective-date">
        Effective Date: <?php echo date('F j, Y'); ?>
      </div>
    </div>

    <div class="terms-content">
      <div class="highlight-box">
        <h3><i class="bi bi-info-circle"></i> Important Notice</h3>
        <p>By accessing and using the CORE II Freight Management System, you agree to be bound by these Terms and Conditions. Please read them carefully before using our services.</p>
      </div>

      <h3 class="section-title">1. Acceptance of Terms</h3>
      <p>These Terms and Conditions ("Terms") govern your use of the CORE II Freight Management System ("System", "Platform", "Service") operated by our organization. By accessing or using our System, you agree to be bound by these Terms and all applicable laws and regulations.</p>

      <h3 class="section-title">2. System Description</h3>
      <p>CORE II is a comprehensive freight management platform that provides:</p>
      <ul>
        <li>Shipment booking and tracking services</li>
        <li>Service provider network management</li>
        <li>Route planning and scheduling</li>
        <li>Tariff and pricing management</li>
        <li>Standard Operating Procedures (SOP) management</li>
        <li>Real-time logistics monitoring</li>
        <li>Administrative and reporting tools</li>
      </ul>

      <h3 class="section-title">3. User Roles and Responsibilities</h3>
      
      <div class="subsection">
        <h4>3.1 System Administrators</h4>
        <ul>
          <li>Have full access to system configuration and management</li>
          <li>Responsible for user account management and security</li>
          <li>Must maintain confidentiality of administrative credentials</li>
          <li>Accountable for system integrity and data protection</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>3.2 Service Providers</h4>
        <ul>
          <li>Must provide accurate service information and capabilities</li>
          <li>Responsible for maintaining service quality standards</li>
          <li>Must comply with all applicable transportation regulations</li>
          <li>Required to update service availability and pricing promptly</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>3.3 End Users</h4>
        <ul>
          <li>Must provide accurate booking and shipment information</li>
          <li>Responsible for compliance with shipping regulations</li>
          <li>Must not misuse the system or attempt unauthorized access</li>
          <li>Required to report system issues or security concerns</li>
        </ul>
      </div>

      <h3 class="section-title">4. Account Registration and Security</h3>
      <ul>
        <li>Users must provide accurate and complete registration information</li>
        <li>Each user is responsible for maintaining the confidentiality of their account credentials</li>
        <li>Users must notify us immediately of any unauthorized use of their account</li>
        <li>We reserve the right to suspend or terminate accounts that violate these Terms</li>
        <li>Two-factor authentication may be required for enhanced security</li>
      </ul>

      <h3 class="section-title">5. Service Usage and Limitations</h3>
      
      <div class="subsection">
        <h4>5.1 Permitted Use</h4>
        <ul>
          <li>The System may only be used for legitimate logistics and transportation purposes</li>
          <li>Users must comply with all applicable local, national, and international laws</li>
          <li>Commercial use is permitted only for authorized service providers</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>5.2 Prohibited Activities</h4>
        <ul>
          <li>Attempting to gain unauthorized access to system components</li>
          <li>Uploading malicious code or attempting to disrupt system operations</li>
          <li>Using the system for illegal activities or prohibited cargo</li>
          <li>Sharing account credentials with unauthorized parties</li>
          <li>Reverse engineering or attempting to copy system functionality</li>
        </ul>
      </div>

      <h3 class="section-title">6. Data Protection and Privacy</h3>
      <ul>
        <li>We collect and process personal data in accordance with applicable privacy laws</li>
        <li>User data is protected using industry-standard security measures</li>
        <li>We do not sell or share personal information with third parties without consent</li>
        <li>Users have the right to access, correct, or delete their personal data</li>
        <li>System logs and usage data may be retained for security and operational purposes</li>
      </ul>

      <h3 class="section-title">7. Service Availability and Maintenance</h3>
      <ul>
        <li>We strive to maintain 99.9% system uptime but cannot guarantee uninterrupted service</li>
        <li>Scheduled maintenance will be announced in advance when possible</li>
        <li>We are not liable for service interruptions due to circumstances beyond our control</li>
        <li>Emergency maintenance may be performed without prior notice</li>
      </ul>

      <h3 class="section-title">8. Intellectual Property Rights</h3>
      <ul>
        <li>All system software, designs, and content are protected by intellectual property laws</li>
        <li>Users are granted a limited, non-exclusive license to use the System</li>
        <li>Users retain ownership of their data but grant us necessary rights to provide services</li>
        <li>Unauthorized copying or distribution of system components is prohibited</li>
      </ul>

      <h3 class="section-title">9. Limitation of Liability</h3>
      <div class="highlight-box">
        <p><strong>Important:</strong> Our liability is limited to the maximum extent permitted by law. We are not responsible for indirect, incidental, or consequential damages arising from system use.</p>
      </div>
      <ul>
        <li>We provide the System "as is" without warranties of any kind</li>
        <li>Users assume responsibility for their use of the System</li>
        <li>Our maximum liability is limited to the fees paid for services</li>
        <li>We are not liable for third-party actions or service provider performance</li>
      </ul>

      <h3 class="section-title">10. Indemnification</h3>
      <p>Users agree to indemnify and hold harmless our organization from any claims, damages, or expenses arising from:</p>
      <ul>
        <li>Violation of these Terms and Conditions</li>
        <li>Misuse of the System or services</li>
        <li>Violation of applicable laws or regulations</li>
        <li>Infringement of third-party rights</li>
      </ul>

      <h3 class="section-title">11. Termination</h3>
      <ul>
        <li>Either party may terminate service with appropriate notice</li>
        <li>We may immediately suspend accounts for Terms violations</li>
        <li>Upon termination, user access will be revoked</li>
        <li>Data retention policies will apply after account termination</li>
      </ul>

      <h3 class="section-title">12. Modifications to Terms</h3>
      <ul>
        <li>We reserve the right to modify these Terms at any time</li>
        <li>Users will be notified of significant changes</li>
        <li>Continued use of the System constitutes acceptance of modified Terms</li>
        <li>Users may terminate their account if they disagree with modifications</li>
      </ul>

      <h3 class="section-title">13. Legal Compliance and Applicable Philippine Laws</h3>
      <p>The CORE II Freight Management System operates in compliance with the following Philippine Republic Acts and regulations:</p>
      
      <div class="subsection">
        <h4>13.1 Data Privacy and Protection</h4>
        <ul>
          <li><strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong> - Governs the collection, processing, and storage of personal data within our system</li>
          <li><strong>Republic Act No. 8792 (E-Commerce Act of 2000)</strong> - Regulates electronic transactions and digital signatures in our platform</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>13.2 Transportation and Logistics</h4>
        <ul>
          <li><strong>Republic Act No. 4136 (Land Transportation and Traffic Code)</strong> - Governs land transportation services and vehicle operations</li>
          <li><strong>Republic Act No. 9295 (Domestic Shipping Development Act of 2004)</strong> - Regulates domestic shipping and maritime transport services</li>
          <li><strong>Republic Act No. 776 (Civil Aeronautics Act)</strong> - Covers air transportation and cargo services where applicable</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>13.3 Consumer Protection and Business Operations</h4>
        <ul>
          <li><strong>Republic Act No. 7394 (Consumer Act of the Philippines)</strong> - Protects consumer rights and establishes fair business practices</li>
          <li><strong>Republic Act No. 8799 (Securities Regulation Code)</strong> - Governs corporate governance and business transparency requirements</li>
          <li><strong>Republic Act No. 11032 (Ease of Doing Business Act)</strong> - Streamlines business processes and regulatory compliance</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>13.4 Anti-Money Laundering and Security</h4>
        <ul>
          <li><strong>Republic Act No. 9160 (Anti-Money Laundering Act of 2001)</strong> as amended by RA 9194, 10167, and 10365 - Requires compliance with anti-money laundering measures</li>
          <li><strong>Republic Act No. 10175 (Cybercrime Prevention Act of 2012)</strong> - Addresses cybersecurity and online fraud prevention</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>13.5 Labor and Employment</h4>
        <ul>
          <li><strong>Presidential Decree No. 442 (Labor Code of the Philippines)</strong> - Governs employment relationships with service providers and staff</li>
          <li><strong>Republic Act No. 11261 (First Time Jobseekers Assistance Act)</strong> - Supports employment opportunities within our network</li>
        </ul>
      </div>

      <div class="subsection">
        <h4>13.6 Environmental and Safety Compliance</h4>
        <ul>
          <li><strong>Presidential Decree No. 1586 (Environmental Impact Assessment System)</strong> - Ensures environmental compliance in logistics operations</li>
          <li><strong>Republic Act No. 6969 (Toxic Substances and Hazardous and Nuclear Wastes Control Act)</strong> - Regulates transport of hazardous materials</li>
        </ul>
      </div>

      <h3 class="section-title">14. Governing Law and Dispute Resolution</h3>
      <ul>
        <li>These Terms are governed by the laws of the Republic of the Philippines</li>
        <li>All disputes shall be subject to the exclusive jurisdiction of Philippine courts</li>
        <li>Users agree to attempt good-faith resolution through mediation before pursuing litigation</li>
        <li>Alternative Dispute Resolution mechanisms under Republic Act No. 9285 (Alternative Dispute Resolution Act of 2004) may be utilized</li>
        <li>Venue for legal proceedings shall be in the appropriate courts within the Philippines</li>
      </ul>

      <h3 class="section-title">15. Contact Information</h3>
      <p>For questions about these Terms and Conditions or the CORE II System, please contact:</p>
      <div class="highlight-box">
        <p><strong>CORE II Support Team</strong><br>
        Email: supportcore2@slatefreight.com<br>
        Phone: 092232136792<br>
        Address: Road 20 Bahay Toro Quezon City</p>
      </div>

      <h3 class="section-title">16. Acknowledgment</h3>
      <p>By using the CORE II Freight Management System, you acknowledge that you have read, understood, and agree to be bound by these Terms and Conditions. These Terms constitute the entire agreement between you and our organization regarding the use of the System.</p>

      <div class="text-center mt-4 pt-4 border-top">
        <p class="text-muted">
          <small>Last updated: <?php echo date('F j, Y'); ?> | Version 1.0</small>
        </p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
