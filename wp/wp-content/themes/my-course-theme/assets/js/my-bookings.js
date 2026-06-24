document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-booking-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bookingId = this.dataset.bookingId;
                if (!confirm('Are you sure you want to cancel this booking?')) return;

                this.disabled = true;
                this.textContent = '⏳ Cancelling...';

                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'cancel_booking',
                            booking_id: bookingId,
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Booking cancelled successfully!');
                            location.reload();
                        } else {
                            alert('❌ ' + data.message);
                            this.disabled = false;
                            this.textContent = '❌ Cancel';
                        }
                    })
                    .catch(err => {
                        alert('❌ Error cancelling booking');
                        this.disabled = false;
                        this.textContent = '❌ Cancel';
                    });
            });
        });
    });