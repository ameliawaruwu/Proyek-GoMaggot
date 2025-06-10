<?php include '../partials/headers.php'; ?>
<?php include '../logic/update/auth.php'; ?>

<link rel="stylesheet" href="../Admin-HTML/css/contact.css">
<link rel="stylesheet" href="../Admin-HTML/css/footer.css">

        <!-- HEADER SECTION-->
            <header class="hero">
                <div class="overlay">
                    <h1>Contact Us</h1>
                    <p>We'd love to hear from you. Feel free to reach out with your inquiries.</p>
                    <a href="#contact-form" class="btn">Get in Touch</a>
                </div>
            </header>
        
            <main>
                <section class="contact-details">
                    <div class="detail">
                        <h3>Our Address</h3>
                        <p>Jl. Asia Afrika, Bandung Kota, Jawa Barat</p>
                    </div>
                    <div class="detail">
                        <h3>Call Us</h3>
                        <p>+62 22 123 4567</p>
                    </div>
                    <div class="detail">
                        <h3>Email Us</h3>
                        <p>info@gomaggot.com</p>
                    </div>
                </section>
        
                <section id="contact-form" class="contact-form">
                    <h2>Get In Touch</h2>
                    <form action="#" method="post">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <textarea name="message" rows="6" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit">Send Message</button>
                    </form>
                </section>
        
                <section class="map">
                    <h2>Our Location</h2>
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.56400419844!2d107.56075541709427!3d-6.903442379398508!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e6398252477f%3A0x146a1f93d3e815b2!2sBandung%2C%20Kota%20Bandung%2C%20Jawa%20Barat!5e0!3m2!1sid!2sid!4v1735923313102!5m2!1sid!2sid" 
                        width="100%" height="550" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </section>
            </main>
        

<script src="../Admin-HTML/js/script.js"></script>

<?php include '../partials/footer.php'; ?>