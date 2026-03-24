     let isPunchedIn = false;
        let startTime = null;
        let timerInterval = null;

        // Mobile Menu Functions
        function toggleMobileMenu() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('mobileOverlay');
            const icon = document.getElementById('mobileMenuIcon');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        function closeMobileMenu() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Punch In/Out Functions
        function togglePunch() {
            const btn = document.getElementById('punchBtn');
            const statusActive = document.getElementById('attendanceStatus');
            const statusInactive = document.getElementById('inactiveStatus');
            const clockInText = document.getElementById('clockInText');

            if (!isPunchedIn) {
                // Punch In
                isPunchedIn = true;
                startTime = new Date();
                
                btn.innerHTML = '<span class="material-symbols-outlined">logout</span> Check Out';
                btn.classList.remove('btn-checkin');
                btn.classList.add('btn-checkout');
                
                statusActive.style.display = 'inline-flex';
                statusInactive.style.display = 'none';
                
                const timeStr = startTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                clockInText.innerHTML = `You clocked in at <span class="clock-in-time">${timeStr}</span>`;
                
                startTimer();
            } else {
                // Punch Out
                isPunchedIn = false;
                stopTimer();
                
                btn.innerHTML = '<span class="material-symbols-outlined">login</span> Check In';
                btn.classList.remove('btn-checkout');
                btn.classList.add('btn-checkin');
                
                statusActive.style.display = 'none';
                statusInactive.style.display = 'inline-flex';
                
                clockInText.innerHTML = 'Click below to start your work day';
                
                // Reset timer display
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
            }
        }

        function startTimer() {
            timerInterval = setInterval(updateTimer, 1000);
        }

        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }

        function updateTimer() {
            if (!startTime) return;

            const now = new Date();
            const diff = now - startTime;

            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

            // Update hours today stat
            const hoursToday = document.getElementById('hoursToday');
            hoursToday.textContent = `${hours}h ${minutes}m`;
            
            // Update progress bar (assuming 8 hour work day)
            const totalMinutes = (hours * 60) + minutes;
            const percentage = Math.min((totalMinutes / 480) * 100, 100);
            document.getElementById('hoursProgress').style.width = percentage + '%';
        }

        function updateTaskStatus(select) {
            const selectedValue = select.value;
            
            // Remove all status classes
            select.classList.remove('status-pending', 'status-progress', 'status-completed');
            
            // Add appropriate class based on selection
            if (selectedValue === 'pending') {
                select.classList.add('status-pending');
            } else if (selectedValue === 'progress') {
                select.classList.add('status-progress');
            } else if (selectedValue === 'completed') {
                select.classList.add('status-completed');
            }
        }