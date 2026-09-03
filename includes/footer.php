<?php
/**
 * Customer Footer Component
 * BongoStore BD
 */
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col">
                <div class="brand-logo" style="color: #34d399; margin-bottom: 16px;">
                    <i class="fa-solid fa-laptop-code" style="color: var(--accent);"></i>
                    <span>Arif Shikder <span style="color: #ffffff;">IT Services</span></span>
                </div>
                <p style="font-size: 0.92rem; line-height: 1.6; margin-bottom: 20px;">
                    বাংলাদেশের নির্ভরযোগ্য আইটি সল্যুশন, কম্পিউটার গ্যাজেট ও ইলেকট্রনিক্স অনলাইন স্টোর। জেনুইন পণ্য, সুলভ মূল্য এবং ঢাকা সহ দেশের ৬৪টি জেলায় দ্রুত ও নিরাপদ হোম ডেলিভারি সুবিধা।
                </p>
                <div class="contact-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span><?= STORE_ADDRESS ?></span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-phone"></i>
                    <span>হটলাইন: <strong><?= STORE_PHONE ?></strong> (সকাল ৯টা - রাত ১০টা)</span>
                </div>
                <div class="contact-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span><?= STORE_EMAIL ?></span>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h5>প্রয়োজনীয় লিংক</h5>
                <ul class="footer-links">
                    <li><a href="/"><i class="fa-solid fa-angle-right"></i> হোম পেজ</a></li>
                    <li><a href="/shop.php"><i class="fa-solid fa-angle-right"></i> সকল পণ্য শপ</a></li>
                    <li><a href="/cart.php"><i class="fa-solid fa-angle-right"></i> শপিং কার্ট</a></li>
                    <li><a href="/checkout.php"><i class="fa-solid fa-angle-right"></i> অর্ডার চেকআউট</a></li>
                    <li><a href="/shop.php?filter=deals"><i class="fa-solid fa-angle-right"></i> বিশেষ ছাড় অফার</a></li>
                </ul>
            </div>

            <!-- Customer Service -->
            <div class="footer-col">
                <h5>গ্রাহক সেবা</h5>
                <ul class="footer-links">
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> ডেলিভারি পলিসি</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> রিটার্ন ও রিফান্ড পলিসি</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> প্রাইভেসী পলিসি</a></li>
                    <li><a href="#"><i class="fa-solid fa-angle-right"></i> শর্তাবলী ও নিয়ম</a></li>
                    <li><a href="/admin/login.php"><i class="fa-solid fa-lock"></i> অ্যাডমিন লগইন</a></li>
                </ul>
            </div>

            <!-- Delivery & Payment Badges -->
            <div class="footer-col">
                <h5>পেমেন্ট ও ডেলিভারি</h5>
                <p style="font-size: 0.88rem; margin-bottom: 14px;">
                    আমরা প্রদান করছি পণ্য হাতে পেয়ে টাকা পরিশোধ করার সম্পূর্ণ নিরাপদ <strong>ক্যাশ অন ডেলিভারি (Cash on Delivery)</strong> সুবিধা।
                </p>
                
                <div class="payment-badges">
                    <span class="payment-badge-pill" style="background: #047857; border-color: #059669;">
                        <i class="fa-solid fa-hand-holding-dollar"></i> ক্যাশ অন ডেলিভারি (COD)
                    </span>
                    <span class="payment-badge-pill">bKash (বিকাশ)</span>
                    <span class="payment-badge-pill">Nagad (নগদ)</span>
                    <span class="payment-badge-pill">Rocket (রকেট)</span>
                </div>

                <div style="margin-top: 20px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                    <div style="font-weight: 700; color: #fff; font-size: 0.85rem; margin-bottom: 4px;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--accent);"></i> ১০০% নিরাপদ শপিং
                    </div>
                    <div style="font-size: 0.8rem; color: #cbd5e1;">সরাসরি রাইডারের কাছ থেকে পণ্য চেক করে মূল্য পরিশোধ করুন।</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Copyright -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <div>
                &copy; <?= date('Y') ?> <strong><?= STORE_NAME ?></strong>. সর্বস্বত্ব সংরক্ষিত। InfinityFree PHP/MySQL হোস্ট রেডি।
            </div>
            <div style="color: #cbd5e1; font-size: 0.82rem;">
                বাংলাদেশী মুদ্রায় হিসাব: <strong>৳ (BDT)</strong>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="/assets/js/main.js"></script>

</body>
</html>
