document.addEventListener("DOMContentLoaded", function () {
  let selected = [];

  const radios = document.querySelectorAll(".week-radio");
  const panel = document.querySelector(".selected-list");
  const clearBtn = document.querySelector(".btn-secondary");
  const submitBtn = document.querySelector(".btn-primary");

  // Syncs the 'selected' array perfectly with checked DOM elements
  function syncSelected() {
    selected = [];
    document.querySelectorAll(".week-radio:checked").forEach((radio) => {
      if (!radio.closest(".class-card").classList.contains("booked")) {
        selected.push({
          course_id: radio.dataset.courseId || 0,
          subject: radio.dataset.subject,
          teacher: radio.dataset.teacher,
          day: radio.dataset.day,
          time: radio.dataset.time,
          week: radio.dataset.week,
          booking_date: radio.dataset.bookingDate,
        });
      }
    });
    renderPanel();
  }

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
      
      // Add subject class for potential CSS grouping (e.g., .subject-math)
      if (item.subject) {
        const subjectClass = "subject-" + item.subject.toLowerCase().replace(/[^a-z0-9]+/g, "-");
        div.classList.add(subjectClass);
      }

      div.innerHTML = `
        <div>
          <strong>${item.subject}</strong> - ${item.teacher}
          <br>
          ${item.day} ${item.time}
          <br>
          Date: ${item.booking_date}
        </div>
      `;
      
      panel.appendChild(div);
    });
  }

  // Applies CONFLICT state to duplicate subjects and timeslots
  function handleLiveConflicts() {
    const checkedRadios = Array.from(radios).filter(
      (r) => r.checked && !r.closest(".class-card").classList.contains("booked")
    );
    
    const activeCards = checkedRadios.map((r) => r.closest(".class-card"));
    const activeSubjects = checkedRadios.map((r) => r.dataset.subject);
    const activeSlots = checkedRadios.map((r) => r.dataset.day + "-" + r.dataset.time);

    // Grab subjects/slots that are statically booked by the user
    const staticBookedCards = document.querySelectorAll(".class-card.booked");
    staticBookedCards.forEach((card) => {
      let sub = card.getAttribute("data-subject");
      if (sub && !activeSubjects.includes(sub)) activeSubjects.push(sub);

      let d = card.getAttribute("data-day");
      let t = card.getAttribute("data-time");
      if (d && t) {
        const slotKey = d.trim() + "-" + t.trim();
        if (!activeSlots.includes(slotKey)) activeSlots.push(slotKey);
      }
    });

    const allCards = document.querySelectorAll(".class-card");

    allCards.forEach((card) => {
      // Skip static PHP states
      if (
        card.classList.contains("booked") ||
        card.classList.contains("course-conflict") ||
        card.classList.contains("disabled") ||
        card.classList.contains("fully-booked")
      ) {
        return; 
      }

      const cardRadios = card.querySelectorAll(".week-radio");
      const cardSubject = card.getAttribute("data-subject");
      const cardSlotKey = card.getAttribute("data-day") + "-" + card.getAttribute("data-time");

      // 1. This card is currently selected by the user
      if (activeCards.includes(card)) {
        card.classList.remove("live-conflict");
        card.style.border = "2px solid #007bff";
        card.style.backgroundColor = "#e7f3ff";
        card.style.opacity = "1";
        
        cardRadios.forEach(r => r.disabled = false);

        const liveBadge = card.querySelector(".badge.live-conflict-badge");
        if (liveBadge) liveBadge.remove();
        return;
      }

      // 2. This card conflicts with an active subject or timeslot
      if (
        activeSubjects.includes(cardSubject) ||
        activeSlots.includes(cardSlotKey)
      ) {
        card.classList.add("live-conflict");
        card.style.border = "2px solid rgb(242 203 146 / 88%)";
        card.style.backgroundColor = "white";
        card.style.opacity = "0.7";
        
        // DISABLE the radio buttons to prevent clicking
        cardRadios.forEach(r => r.disabled = true);
        
        if (!card.querySelector(".badge.conflict")) {
          const badge = document.createElement("div");
          badge.className = "badge conflict live-conflict-badge";
          badge.innerText = "CONFLICT";
          const teacherDiv = card.querySelector(".class-teacher");
          if (teacherDiv) teacherDiv.parentNode.insertBefore(badge, teacherDiv.nextSibling);
        }
      } 
      // 3. This card is fully available
      else {
        card.classList.remove("live-conflict");
        card.style.border = "";
        card.style.backgroundColor = "";
        card.style.opacity = "";
        
        // ENABLE the radio buttons
        cardRadios.forEach(r => r.disabled = false);

        const liveBadge = card.querySelector(".badge.live-conflict-badge");
        if (liveBadge) liveBadge.remove();
      }
    });
  }

  // Radio button toggle logic (bypasses default browser behavior)
  radios.forEach((radio) => {
    // Record state right before click
    radio.addEventListener("mousedown", function () {
      this.dataset.wasChecked = this.checked ? "true" : "false";
    });

    radio.addEventListener("click", function (e) {
      if (this.closest(".class-card").classList.contains("booked")) return;

      if (this.dataset.wasChecked === "true") {
        // Deselect
        this.checked = false;
        this.dataset.wasChecked = "false";
      } else {
        // Select
        this.dataset.wasChecked = "true";
        const name = this.getAttribute("name");
        document.querySelectorAll(`input[name="${name}"]`).forEach((r) => {
          if (r !== this) r.dataset.wasChecked = "false";
        });
      }

      syncSelected(); 
      handleLiveConflicts(); 
    });
  });

  if (clearBtn) {
    clearBtn.addEventListener("click", function (e) {
      e.preventDefault();
      radios.forEach((radio) => {
        if (!radio.closest(".class-card").classList.contains("booked")) {
          radio.checked = false;
          radio.dataset.wasChecked = "false";
        }
      });
      syncSelected(); 
      handleLiveConflicts(); 
    });
  }

  if (submitBtn) {
    submitBtn.addEventListener("click", function () {
      if (selected.length === 0) {
        alert("No bookings selected.");
        return;
      }

      fetch(wpData.ajaxUrl, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          action: "submit_booking",
          selected: JSON.stringify(selected),
        }),
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            submitBtn.innerText = "Booking Successful!";
            submitBtn.style.backgroundColor = "#28a745";
            submitBtn.disabled = true;
            alert("Booking successfully saved!");
            location.reload();
          } else {
            alert("Error: " + (data.data.message || "Unknown error"));
          }
        })
        .catch((err) => {
          console.error(err);
          alert("Error submitting booking.");
        });
    });
  }

  // Auth Modals
  const loginModal = document.getElementById("login-modal");
  const overlay = document.getElementById("auth-overlay");

  function openLoginModal() {
    if (loginModal && overlay) {
      loginModal.classList.remove("hidden");
      overlay.classList.remove("hidden");
    }
  }

  function closeModal() {
    if (loginModal && overlay) {
      loginModal.classList.add("hidden");
      overlay.classList.add("hidden");
    }
  }

  if (overlay) overlay.addEventListener("click", closeModal);
  document.querySelectorAll(".close-modal").forEach((btn) => {
    btn.addEventListener("click", closeModal);
  });
  document.querySelectorAll(".login-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      openLoginModal();
    });
  });

  if (typeof wpData !== 'undefined' && !wpData.isLoggedIn) {
    openLoginModal();
  }

  // Initialize
  syncSelected();
  handleLiveConflicts();
});