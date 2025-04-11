<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibReserve - Smart Library Table Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
            scroll-behavior: smooth;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        /* Top Banner */
        .top-banner {
            background-color: #a01515;
            color: white;
            text-align: center;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1001;
        }
        
        .institution-details {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        
        .institution-details i {
            font-size: 1.1rem;
            margin: 0 0.3rem;
        }
        
        /* Header */
        header {
            background-color: #b91a1a;
            color: white;
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            top: 0;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 2rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #f0f0f0;
        }
        
        .login-btn {
            background-color: transparent;
            border: 2px solid white;
            border-radius: 4px;
            padding: 0.5rem 1rem;
        }
        
        .login-btn i {
            margin-right: 5px;
        }
        
        /* Dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            z-index: 1;
            border-radius: 5px;
        }
        
        .dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }
        
        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #b91a1a;
        }
        
        .dropdown:hover .dropdown-content {
            display: block;
        }
        
        /* Add padding and pseudo-element to create invisible area between button and dropdown */
        .dropdown::after {
            content: '';
            position: absolute;
            height: 20px;
            width: 100%;
            top: 100%;
            left: 0;
        }
        
        /* COMPACT HERO SECTION - LIGHT GREY */
        .hero {
            height: auto;
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            color: #333;
            padding: 80px 5% 60px;
            overflow: hidden;
        }
        
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(240, 240, 240, 0.85);
            z-index: 1;
        }
        
        .hero-content {
            max-width: 800px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 0;
            letter-spacing: 1px;
            color: #333;
        }
        
        .hero h1.title-line {
            font-size: 3.8rem;
            margin-top: 0;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
            color: #b91a1a;
        }
        
        .hero h1.title-line::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #b91a1a;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin: 0.8rem 0 1.5rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            color: #555;
        }
        
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            min-width: 160px;
            font-size: 1rem;
        }
        
        .cta-button i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .primary-btn {
            background-color: #b91a1a;
            color: white;
            border: none;
        }
        
        .primary-btn:hover {
            background-color: #7f1d1d;
        }
        
        .secondary-btn {
            background-color: transparent;
            color: #333;
            border: 1px solid #333;
        }
        
        .secondary-btn:hover {
            background-color: #333;
            color: white;
        }
        
        .hero-features {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
            margin-top: 1.5rem;
        }
        
        .feature-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: rgba(185, 26, 26, 0.1);
            padding: 1rem;
            width: 130px;
            transition: all 0.3s ease;
            border: 1px solid rgba(185, 26, 26, 0.2);
        }
        
        .feature-box:hover {
            background-color: rgba(185, 26, 26, 0.2);
        }
        
        .feature-box i {
            font-size: 1.8rem;
            margin-bottom: 0.7rem;
            color: #b91a1a;
        }
        
        .feature-box span {
            font-weight: 600;
            font-size: 0.95rem;
            color: #333;
            text-align: center;
        }
        
        .scroll-down {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #555;
            text-align: center;
            z-index: 2;
            cursor: pointer;
            width: 100%;
        }
        
        .scroll-down p {
            font-size: 0.75rem;
            margin-bottom: 0.3rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            font-family: 'Arial', sans-serif;
            opacity: 0.8;
        }
        
        .scroll-down i {
            font-size: 1.2rem;
            color: #b91a1a;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero h1.title-line {
                font-size: 3rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                gap: 0.8rem;
                align-items: center;
            }
            
            .hero-features {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .feature-box {
                width: 120px;
                margin-bottom: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-features {
                flex-direction: column;
                align-items: center;
            }
            
            .feature-box {
                width: 80%;
                max-width: 160px;
            }
        }
        
        /* Main Content */
        section {
            padding: 5rem 10%;
        }
        
        section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            color: #b91a1a;
        }
        
        section p {
            margin-bottom: 1.5rem;
        }
        
        /* Features */
        .features {
            background-color: white;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 3rem 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.4s, box-shadow 0.4s;
            background: linear-gradient(to bottom, white, #f9f9f9);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #b91a1a;
        }
        
        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .feature-icon {
            font-size: 6rem;
            color: #b91a1a;
            margin-bottom: 2rem;
            transition: transform 0.5s;
            display: inline-block;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotate(5deg);
        }
        
        .feature-card h3 {
            margin-bottom: 1rem;
        }
        
        /* How It Works */
        .how-it-works {
            background-color: #f8f8f8;
        }
        
        .steps {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .step-number {
            font-size: 2rem;
            font-weight: bold;
            background-color: #b91a1a;
            color: white;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 2rem;
        }
        
        /* FAQ */
        .faq {
            background-color: white;
        }
        
        .faq-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .faq-item h3 {
            color: #b91a1a;
            margin-bottom: 0.5rem;
        }
        
        /* Contact */
        .contact {
            background-color: #f8f8f8;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .contact-info {
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .contact-info:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .contact-info h3 {
            color: #b91a1a;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f1f1;
        }
        
        .contact-info p {
            margin-bottom: 0.5rem;
            transition: transform 0.2s;
        }
        
        .contact-info p:hover {
            transform: translateX(5px);
        }
        
        .contact-info p i {
            color: #b91a1a;
            width: 25px;
            margin-right: 8px;
        }
        
        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .social-icons a {
            color: #b91a1a;
            font-size: 1.5rem;
            transition: color 0.3s, transform 0.3s;
        }
        
        .social-icons a:hover {
            color: #7f1d1d;
            transform: scale(1.2);
        }
        
        /* Footer */
        footer {
            background-color: #b91a1a;
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        footer p {
            margin: 0.5rem 0;
        }
        
        /* Institution info for footer */
        .institution-info {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            padding: 0 3rem;
        }
        
        .institution-info:before,
        .institution-info:after {
            content: '❝';
            font-size: 2.5rem;
            color: white;
            position: absolute;
            opacity: 0.7;
        }
        
        .institution-info:before {
            top: 1rem;
            left: 0;
        }
        
        .institution-info:after {
            bottom: -1.5rem;
            right: 0;
            transform: rotate(180deg);
        }
        
        .institution-info p {
            margin: 0.3rem 0;
            font-size: 0.9rem;
            font-style: italic;
        }
        
        .institution-info p:first-of-type {
            font-style: normal;
            font-weight: bold;
            font-size: 0.95rem;
        }
        
        .institution-info i {
            margin-right: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 1rem;
            }
            
            .nav-links {
                margin-top: 1rem;
            }
            
            .nav-links li {
                margin-left: 1rem;
                margin-right: 1rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            section {
                padding: 3rem 5%;
            }
        }
        
        /* Image Showcase */
        .image-showcase {
            background-color: white;
            text-align: center;
            padding: 5rem 0;
        }
        
        .showcase-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 3rem auto;
            max-width: 1200px;
            flex-wrap: wrap;
        }
        
        .showcase-item {
            width: 300px;
            margin: 1.5rem;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .showcase-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .showcase-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .showcase-caption {
            padding: 1.5rem;
            background: white;
        }
        
        .showcase-caption h3 {
            color: #b91a1a;
            margin-bottom: 0.5rem;
        }
        
        /* Logo Banner */
        .logo-banner {
            background-color: white;
            padding: 3rem 0;
            text-align: center;
        }
        
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .big-logo {
            font-size: 10rem;
            color: #b91a1a;
            margin: 0 2rem;
        }
        
        .logo-text {
            text-align: left;
        }
        
        .logo-text h2 {
            font-size: 3.5rem;
            color: #b91a1a;
            margin-bottom: 1rem;
        }
        
        .logo-text p {
            font-size: 1.2rem;
            max-width: 500px;
        }
        
        /* Reading Hall Hours */
        .hours-section {
            margin-top: 15px;
        }
        
        .hours-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #b91a1a;
            font-weight: bold;
        }
        
        .hours-header i {
            color: #b91a1a;
            width: 25px;
            margin-right: 8px;
        }
        
        .hours-time {
            padding-left: 33px;
            margin-bottom: 15px;
        }
        
        .contact-info .location-info {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .contact-info .location-icon {
            color: #b91a1a;
            margin-right: 8px;
            width: 25px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 3px;
        }
        
        .contact-info .location-text {
            flex: 1;
            line-height: 1.5;
        }
        
        /* About Section */
        .about-section {
            background: linear-gradient(135deg, #f5f5f5 0%, #e6e6e6 100%);
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .about-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            z-index: 0;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.7) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 60s infinite linear;
        }
        
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-header h2 {
            color: #b91a1a;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }
        
        .about-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #b91a1a;
        }
        
        .about-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .about-card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }
        
        .about-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: #b91a1a;
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }
        
        .about-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .about-card:hover::before {
            transform: scaleX(1);
        }
        
        .about-card h3 {
            color: #b91a1a;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .about-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 2px;
            background-color: #b91a1a;
            transition: width 0.4s ease;
        }
        
        .about-card:hover h3::after {
            width: 100px;
        }
        
        .about-card p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            transition: color 0.3s ease;
        }
        
        .about-card:hover p {
            color: #333;
        }
        
        .about-card .read-more {
            color: #b91a1a;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .about-card .read-more i {
            margin-left: 8px;
            transition: transform 0.3s ease;
        }
        
        .about-card:hover .read-more {
            color: #7f1d1d;
        }
        
        .about-card:hover .read-more i {
            transform: translateX(5px);
        }
        
        .about-text {
            height: 300px;
            overflow-y: auto;
            padding-right: 10px;
            position: relative;
        }
        
        .about-text::-webkit-scrollbar {
            width: 6px;
        }
        
        .about-text::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .about-text::-webkit-scrollbar-thumb {
            background: #b91a1a;
            border-radius: 10px;
        }
        
        .about-text::-webkit-scrollbar-thumb:hover {
            background: #7f1d1d;
        }
        
        @media (max-width: 992px) {
            .about-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.querySelector('.dropdown > a');
        const dropdownContent = document.querySelector('.dropdown-content');
        
        // Add direct click event for admin login in dropdown
        const adminLoginLink = document.querySelector('.dropdown-content a[href="admin-login.php"]');
        adminLoginLink.addEventListener('click', function(e) {
            window.location.href = 'admin-login.php';
        });
        
        let isOpen = false;
        
        dropdownBtn.addEventListener('click', function(e) {
            e.preventDefault();
            isOpen = !isOpen;
            
            if (isOpen) {
                dropdownContent.style.display = 'block';
            } else {
                dropdownContent.style.display = 'none';
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                dropdownContent.style.display = 'none';
                isOpen = false;
            }
        });
    });
    </script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">LibReserve</div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#faq">FAQ</a></li>
                <li><a href="#contact">Contact</a></li>
                <li class="dropdown">
                    <a href="#" class="login-btn"><i class="fas fa-user"></i> Login</a>
                    <div class="dropdown-content">
                        <a href="login.php"><i class="fas fa-user-graduate"></i> Student Login</a>
                        <a href="admin-login.php"><i class="fas fa-user-shield"></i> Admin Login</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Welcome to</h1>
            <h1 class="title-line">LibReserve</h1>
            <p>Smart Library Table Booking System</p>
            
            <div class="hero-buttons">
                <a href="login.php" class="cta-button primary-btn">
                    <i class="fas fa-sign-in-alt"></i> Student Login
                </a>
                <a href="admin-login.php" class="cta-button secondary-btn">
                    <i class="fas fa-user-shield"></i> Admin Login
                </a>
            </div>
            
            <div class="hero-features">
                <div class="feature-box">
                    <i class="fas fa-bolt"></i>
                    <span>Quick Booking</span>
                </div>
                <div class="feature-box">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Mobile Friendly</span>
                </div>
                <div class="feature-box">
                    <i class="fas fa-clock"></i>
                    <span>Real-time Updates</span>
                </div>
            </div>
        </div>
        <div class="scroll-down">
            <p>Scroll to explore</p>
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <section class="logo-banner">
        <div class="logo-container">
            <div class="big-logo">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="logo-text">
                <h2>LibReserve</h2>
                <p>Smart library table booking system that revolutionizes how students access and utilize study spaces.</p>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <h2>Key Features</h2>
        <p>LibReserve offers a comprehensive solution for library table management and reservations. Designed with students and library administrators in mind, our system simplifies the process of finding and booking study spaces.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Easy Booking</h3>
                <p>Reserve your study table with just a few clicks. The intuitive interface makes booking fast and hassle-free.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Visual Table Map</h3>
                <p>See the library layout and available tables in real-time with our interactive map interface.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3>QR Code Verification</h3>
                <p>Receive a QR code for easy check-in and verification of your reservation.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <h3>Reminders & Notifications</h3>
                <p>Get email confirmations and reminders about your upcoming reservations.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3>Reservation History</h3>
                <p>Keep track of your past and upcoming reservations in one place.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Friendly</h3>
                <p>Access LibReserve from any device - works perfectly on desktops, tablets, and smartphones.</p>
            </div>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works">
        <h2>How It Works</h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Create an Account</h3>
                    <p>Sign up with your student credentials to access the LibReserve system.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Browse Available Tables</h3>
                    <p>Check the interactive map to see which tables are available at your preferred time.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Make Your Reservation</h3>
                    <p>Select your table, date, time, and duration to complete your booking.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-content">
                    <h3>Receive Confirmation</h3>
                    <p>Get a confirmation email with your QR code and reservation details.</p>
                </div>
            </div>
            
            <div class="step">
                <div class="step-number">5</div>
                <div class="step-content">
                    <h3>Check In at the Library</h3>
                    <p>Show your QR code when you arrive to confirm your reservation.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="faq" id="faq">
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq-item">
            <h3>How long can I reserve a table for?</h3>
            <p>The standard reservation duration is 1 hour, but you can extend your booking if the table is available after your session.</p>
        </div>
        
        <div class="faq-item">
            <h3>Can I reserve multiple tables at once?</h3>
            <p>To ensure fair usage, each student can only have one active reservation at a time.</p>
        </div>
        
        <div class="faq-item">
            <h3>Can I make a last-minute booking?</h3>
            <p>Yes, you can book a table right up to the moment you need it, as long as there are tables available. Our real-time system shows current availability.</p>
        </div>
        
        <div class="faq-item">
            <h3>How do I use the QR code for my reservation?</h3>
            <p>Once your reservation is confirmed, you'll receive a QR code via email and on the confirmation page. Simply show this code to library staff when you arrive for verification.</p>
        </div>
        
        <div class="faq-item">
            <h3>Can I cancel my reservation?</h3>
            <p>Yes, you can easily cancel your reservation by clicking the 'Cancel Reservation' button on the Confirmation page. Once cancelled, the table becomes immediately available for other students to book.</p>
        </div>
        
        <div class="faq-item">
            <h3>Is my booking information secure?</h3>
            <p>Yes, all reservation data is securely stored and only accessible to authorized personnel. Your privacy is important to us.</p>
        </div>
        
        <div class="faq-item">
            <h3>What happens if I'm late for my reservation?</h3>
            <p>We recommend arriving on time for your scheduled reservation. If you're running late, you can contact library support to hold your table.</p>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="container">
            <h2>About Us</h2>
            <div class="about-cards">
                <div class="about-card">
                    <h3>About LibReserve</h3>
                    <div class="about-text">
                        <p>LibReserve is an innovative library table booking system developed to streamline and modernize the way students access library resources. Our system combines user-friendly design with powerful functionality to create an efficient library space management solution.</p>
                        
                        <p>Key Implementation Features:</p>
                        <ul style="margin-left: 20px; margin-bottom: 15px;">
                            <li>Real-time table booking system with instant confirmation</li>
                            <li>QR code-based verification for secure check-in</li>
                            <li>Automated status updates for reservations</li>
                            <li>Comprehensive admin dashboard with booking analytics</li>
                            <li>Mobile-responsive design for accessibility</li>
                        </ul>
                        
                        <p>Technology Stack:</p>
                        <ul style="margin-left: 20px; margin-bottom: 15px;">
                            <li>Frontend: HTML5, CSS3, JavaScript</li>
                            <li>Backend: PHP with MySQL Database</li>
                            <li>Security: Session Management & Data Encryption</li>
                            <li>Analytics: Real-time Data Processing</li>
                        </ul>
                        
                        <p>Our system ensures efficient space utilization while providing students with a hassle-free booking experience. The implementation focuses on reliability, security, and user experience, making library resource management more accessible than ever.</p>
                    </div>
                </div>
                
                <div class="about-card">
                    <h3>About K. J. Somaiya Institute of Technology</h3>
                    <div class="about-text">
                        <p>The K. J. Somaiya Institute of Technology (KJSIT), was established by the Somaiya Trust in the year 2001 at Ayurvihar campus, Sion.</p>
                        
                        <p>The institute was set up primarily in response to the need for imparting quality education in the modern field of Information Technology and the allied branches of Engineering and Technology.</p>
                        
                        <p>The College is housed in a G+8 storeyed building and in International Standard of Riturang building with airy classrooms, hi-tech laboratories, auditorium, canteen, common rooms etc.</p>
                        
                        <p>KJSIEIT is committed to providing a comprehensive education that combines theoretical knowledge with practical skills, ensuring that students are well-prepared for the challenges of the professional world.</p>
                        
                        <p>With a focus on innovation and excellence, the institute continually updates its curriculum and facilities to keep pace with the rapidly evolving field of technology.</p>
                    </div>
                    <a href="https://kjsieit.somaiya.edu/en" target="_blank" class="read-more">Visit Website <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section class="contact" id="contact">
        <h2>Contact Us</h2>
        
        <div class="contact-grid">
            <div class="contact-info">
                <h3>Get In Touch</h3>
                <div class="location-info">
                    <div class="location-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="location-text">
                        Somaiya Ayurvihar Complex,<br>
                        Sion, Mumbai, India
                    </div>
                </div>
                
                <div class="location-info">
                    <div class="location-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="location-text">
                        91-22-24061408 / 91-22-24061403
                    </div>
                </div>
                
                <div class="location-info">
                    <div class="location-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="location-text">
                        info.tech@somaiya.edu
                    </div>
                </div>
                
                <div class="social-icons">
                    <a href="https://www.facebook.com/kjsit1official" target="_blank"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/kjsieit1" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/kjsit_official" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/kjsieit/" target="_blank"><i class="fab fa-linkedin"></i></a>
                    <a href="https://www.youtube.com/kjsieitofficial" target="_blank"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="contact-info">
                <h3>Support Hours</h3>
                <p><i class="fas fa-clock"></i> Monday - Friday: 8:00 a.m. - 7:00 p.m.</p>
                <p><i class="fas fa-clock"></i> Saturday: 9:00 AM - 5:00 PM</p>
                <p><i class="fas fa-clock"></i> Sunday: Closed</p>
            </div>
            
            <div class="contact-info">
                <h3>Reading Hall</h3>
                <p><i class="fas fa-users"></i> Seating Capacity: 100 students</p>
                
                <div class="hours-section">
                    <div class="hours-header">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Regular Term Hours:</span>
                    </div>
                    <div class="hours-time">
                        Monday to Friday: 8:30 a.m. - 7:00 p.m.
                    </div>
                    
                    <div class="hours-header">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Examination Period:</span>
                    </div>
                    <div class="hours-time">
                        Monday to Friday: 8:30 a.m. - 7:00 p.m.
                    </div>
                    
                    <div class="hours-header">
                        <i class="fas fa-calendar-week"></i>
                        <span>Institute notified holidays:</span>
                    </div>
                    <div class="hours-time">
                        10:00 a.m. - 5:00 p.m.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>© 2025 LibReserve - Smart Library Table Booking System</p>
        <div class="institution-info">
            <p><i class="fas fa-university"></i> An Autonomous Institute</p>
            <p>Permanently Affiliated to the University of Mumbai</p>
            <p>Accredited by NAAC with 'A' Grade (3.21 CGPA)</p>
            <p>Approved by AICTE, New Delhi</p>
        </div>
    </footer>
</body>
</html> 