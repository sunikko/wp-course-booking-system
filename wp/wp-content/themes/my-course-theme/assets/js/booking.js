document.addEventListener("DOMContentLoaded", function () {
  let selected = [];

  const radios = document.querySelectorAll(".week-radio");
  const panel = document.querySelector(".selected-list");

  const clearBtn = document.querySelector(".btn-secondary");

  function renderPanel() {
    panel.innerHTML = "";

    if (selected.length === 0) {
      panel.innerHTML = `
                <div class="empty-state">
                    No bookings selected
                </div>
            `;

      return;
    }

    selected.forEach((item) => {
      const div = document.createElement("div");

      div.className = "selected-item";

      div.innerHTML = `
                <div>
                    <strong>${item.subject}</strong> - ${item.teacher}
                    <br>
                    ${item.day} ${item.time}
                    <br>
                    Week: ${item.week}
                </div>
            `;

      panel.appendChild(div);
    });
  }

  function addOrUpdateBooking(booking) {
    selected = selected.filter((item) => {
      return !(
        item.subject === booking.subject &&
        item.day === booking.day &&
        item.time === booking.time
      );
    });

    selected.push(booking);

    renderPanel();
  }

  function submitBooking() {
    if (selected.length === 0) {
      alert("No bookings selected");
      return;
    }

    const submitBtn = document.getElementById("submit-bookings");
    fetch(wpData.ajaxUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "submit_booking",
        selected: JSON.stringify(selected),
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        console.log("SERVER RESPONSE:", data);

        if (data.success) {
          if (submitBtn) {
            submitBtn.innerText = "✓ Booking Successful!";
            submitBtn.style.backgroundColor = "#28a745";
            submitBtn.disabled = true;
          }

          alert("Booking successfully saved!\n" + data.data.booked.join("\n"));
          location.reload();
        } else {
          alert("Error: " + (data.data.message || "Unknown error"));
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Error submitting booking");
      });
  }

  const loginModal = document.getElementById("login-modal");
  const overlay = document.getElementById("auth-overlay");

  function openLoginModal() {
    loginModal.classList.remove("hidden");
    overlay.classList.remove("hidden");
  }

  function closeModal() {
    loginModal.classList.add("hidden");
    overlay.classList.add("hidden");
  }

  overlay.addEventListener("click", closeModal);

  document.querySelectorAll(".close-modal").forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });

  document.querySelectorAll(".login-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      openLoginModal();
    });
  });
  overlay.addEventListener("click", closeModal);

  document.querySelectorAll(".close-modal").forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });

  const isLoggedIn = wpData.isLoggedIn;

  console.log("LOGIN STATUS:", isLoggedIn);

  if (!isLoggedIn) {
    openLoginModal();
  }
  radios.forEach((radio) => {
    radio.addEventListener("change", function () {
      const booking = {
        course_id: this.dataset.courseId || 0,
        subject: this.dataset.subject,
        teacher: this.dataset.teacher,
        day: this.dataset.day,
        time: this.dataset.time,
        week: this.dataset.week,
      };

      addOrUpdateBooking(booking);
    });
  });

  clearBtn.addEventListener("click", function () {
    selected = [];

    radios.forEach((radio) => {
      radio.checked = false;
    });

    renderPanel();
  });

  document
    .querySelector(".btn-primary")
    .addEventListener("click", submitBooking);

  renderPanel();
});
