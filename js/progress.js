/**
 * Progress Visualization System - Logic & Animations
 */

const PV = {
    init: function() {
        this.observeElements();
    },

    observeElements: function() {
        if (this.observer) {
            this.disconnect();
        }

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateElement(entry.target);
                    // We don't unobserve immediately if we want re-trigger, 
                    // but for one-off animations we should.
                    this.observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        this.scan();
    },

    scan: function() {
        document.querySelectorAll('[data-pv-type]').forEach(el => this.observer.observe(el));
    },

    observe: function(el) {
        if (this.observer && el) this.observer.observe(el);
    },

    disconnect: function() {
        if (this.observer) this.observer.disconnect();
    },

    animateElement: function(el) {
        const type = el.dataset.pvType;
        const percent = parseFloat(el.dataset.percent);

        if (type === 'bar') {
            this.animateBar(el, percent);
        } else if (type === 'ring') {
            this.animateRing(el, percent);
        }
        
        // Trigger generic milestone feedback
        this.checkMilestones(el, percent);
    },

    animateBar: function(el, percent) {
        const fill = el.querySelector('.pv-bar-fill');
        if (fill) {
            // Force reflow
            fill.style.width = '0%';
            setTimeout(() => {
                fill.style.width = percent + '%';
            }, 100);
        }
    },

    animateRing: function(el, percent) {
        const circle = el.querySelector('.pv-ring-circle-fg');
        const circumference = parseFloat(el.dataset.circumference);
        // data-circumference should be around ~339 for r=54
        
        const offset = circumference - (percent / 100) * circumference;
        
        if (circle) {
             // Animate Stroke
            setTimeout(() => {
                circle.style.strokeDashoffset = offset;
            }, 100);
        }

        // Animate Number
        const numEl = el.querySelector('.pv-ring-percentCount');
        if (numEl) {
            this.animateNumber(numEl, 0, Math.round(percent), 1000);
        }
    },

    animateNumber: function(el, start, end, duration) {
        let startTime = null;
        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            el.innerHTML = value;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
               el.innerHTML = end; // Ensure final exact value
            }
        };
        window.requestAnimationFrame(step);
    },

    checkMilestones: function(el, percent) {
        let msg = "";
        if (percent >= 100) msg = "Goal completed 🎉";
        else if (percent >= 75) msg = "Almost done!";
        else if (percent >= 50) msg = "Halfway there";
        else if (percent >= 25) msg = "Great momentum";

        if (msg) {
            // Show toast after animation delay
            setTimeout(() => {
                this.showToast(el, msg);
                // Add glow if 100%
                if (percent >= 100) el.classList.add('pv-milestone-glow');
            }, 800);
        }
    },

    showToast: function(el, msg) {
        const toast = el.querySelector('.pv-feedback-toast');
        if (toast) {
            toast.textContent = msg;
            toast.classList.add('show');
            // Hide after 3s
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    }
};

// Initialize on Load
document.addEventListener('DOMContentLoaded', () => {
    PV.init();
});
