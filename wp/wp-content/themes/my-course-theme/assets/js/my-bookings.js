document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cancel-booking-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const bookingId = this.dataset.bookingId;
            if (!confirm('Are you sure you want to cancel this booking?')) return;

            this.disabled = true;
            this.textContent = '⏳ Cancelling...';

            // Replaced PHP tags with the global wpData.ajaxUrl
            fetch(wpData.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'cancel_booking',
                    booking_id: bookingId,
                    nonce: wpData.nonce,
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Booking cancelled successfully!');
                    location.reload();
                } else {
                    // Adjusted to capture wp_send_json_error format properly
                    alert('❌ ' + (data.data?.message || 'Unknown error'));
                    this.disabled = false;
                    this.textContent = '❌ Cancel';
                }
            })
            .catch(err => {
                console.error(err);
                alert('❌ Error cancelling booking');
                this.disabled = false;
                this.textContent = '❌ Cancel';
            });
        });
    });
});