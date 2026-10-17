<footer class="footer">
    <div class="footer-top">
        <div class="footer-brand">
            <div class="footer-logo">AURUM</div>
            <p>"Time is not measured. It is experienced."</p>
            <div class="social-icons">
                <a href="https://instagram.com" target="_blank" aria-label="Instagram">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="2" y="2" width="20" height="20" rx="5"/>
                        <circle cx="12" cy="12" r="5"/>
                        <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                </a>
                <a href="https://facebook.com" target="_blank" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                    </svg>
                </a>
                <a href="https://twitter.com" target="_blank" aria-label="Twitter">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/>
                    </svg>
                </a>
                <a href="https://youtube.com" target="_blank" aria-label="YouTube">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58 2.78 2.78 0 0 0 1.95 1.96C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/>
                        <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="footer-links-group">
            <h4>Navigate</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php#catalog">Collection</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                    <li><a href="admin.php">Admin Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="footer-links-group">
            <h4>Brands</h4>
            <ul>
                <li><a href="index.php#catalog">Rolex</a></li>
                <li><a href="index.php#catalog">Patek Philippe</a></li>
                <li><a href="index.php#catalog">Audemars Piguet</a></li>
                <li><a href="index.php#catalog">Richard Mille</a></li>
                <li><a href="index.php#catalog">Omega</a></li>
            </ul>
        </div>
        <div class="footer-links-group">
            <h4>Support</h4>
            <ul>
                <li><a href="#">Shipping & Returns</a></li>
                <li><a href="#">Authentication</a></li>
                <li><a href="#">Warranty</a></li>
                <li><a href="#">FAQ</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> AURUM Fine Timepieces. All rights reserved.</p>
        <p>Privacy Policy · Terms of Service · Cookie Policy</p>
    </div>
</footer>