<?php
// Reusable loading overlay for admin pages
?>
<style>
  .loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(44, 62, 80, 0.95) 0%, rgba(52, 73, 94, 0.98) 100%);
    backdrop-filter: blur(20px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .loading-overlay.show { opacity: 1; visibility: visible; }

  .loading-container { text-align: center; position: relative; }

  .loading-logo {
    width: 80px; height: 80px; margin-bottom: 2rem; animation: logoFloat 3s ease-in-out infinite;
  }

  .loading-spinner {
    width: 60px; height: 60px; border: 3px solid rgba(0, 198, 255, 0.2);
    border-top: 3px solid #00c6ff; border-radius: 50%; animation: spin 1s linear infinite;
    margin: 0 auto 1.5rem; position: relative;
  }
  .loading-spinner::before {
    content: ''; position: absolute; top: -3px; left: -3px; right: -3px; bottom: -3px;
    border: 3px solid transparent; border-top: 3px solid rgba(0, 198, 255, 0.4);
    border-radius: 50%; animation: spin 1.5s linear infinite reverse;
  }

  .loading-text { font-size: 1.2rem; font-weight: 600; color: #00c6ff; margin-bottom: 0.5rem; opacity: 0; animation: textFadeIn 0.5s ease-out 0.3s forwards; }
  .loading-subtext { font-size: 0.9rem; color: #b0bec5; opacity: 0; animation: textFadeIn 0.5s ease-out 0.6s forwards; }

  .loading-progress { width: 200px; height: 4px; background: rgba(0, 198, 255, 0.2); border-radius: 2px; margin: 1rem auto 0; overflow: hidden; position: relative; }
  .loading-progress-bar { height: 100%; background: linear-gradient(90deg, #00c6ff, #0072ff); border-radius: 2px; width: 0%; animation: progressFill 2s ease-in-out infinite; }

  .loading-dots { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; }
  .loading-dot { width: 8px; height: 8px; background: #00c6ff; border-radius: 50%; animation: dotPulse 1.4s ease-in-out infinite both; }
  .loading-dot:nth-child(2) { animation-delay: 0.2s; }
  .loading-dot:nth-child(3) { animation-delay: 0.4s; }

  @keyframes spin { 0% { transform: rotate(0deg);} 100% { transform: rotate(360deg);} }
  @keyframes logoFloat { 0%, 100% { transform: translateY(0);} 50% { transform: translateY(-6px);} }
  @keyframes textFadeIn { 0% { opacity: 0; transform: translateY(6px);} 100% { opacity: 1; transform: translateY(0);} }
  @keyframes progressFill { 0% { width: 0%; } 50% { width: 70%; } 100% { width: 100%; } }
  @keyframes dotPulse { 0%, 80%, 100% { transform: scale(0.8); opacity: 0.6; } 40% { transform: scale(1.1); opacity: 1; } }
</style>

<div class="loading-overlay" id="loadingOverlay">
  <div class="loading-container">
    <img src="logo.png" alt="SLATE Logo" class="loading-logo">
    <div class="loading-spinner"></div>
    <div class="loading-text" id="loadingText">Loading...</div>
    <div class="loading-subtext" id="loadingSubtext">Please wait while we prepare your page</div>
    <div class="loading-progress">
      <div class="loading-progress-bar"></div>
    </div>
    <div class="loading-dots">
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
      <div class="loading-dot"></div>
    </div>
  </div>
</div>

<script>
  // Initial page load overlay behavior
  (function(){
    var overlay = document.getElementById('loadingOverlay');
    if (!overlay) return;
    // Show overlay asap
    document.addEventListener('DOMContentLoaded', function(){
      overlay.classList.add('show');
    });
    // Hide when fully loaded
    window.addEventListener('load', function(){
      setTimeout(function(){ overlay.classList.remove('show'); }, 300);
    });

    // Also show on any form submission by default
    document.addEventListener('submit', function(e){
      if (overlay) overlay.classList.add('show');
      var submitBtn = e.target && e.target.querySelector && e.target.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
    }, true);
  })();
</script>
