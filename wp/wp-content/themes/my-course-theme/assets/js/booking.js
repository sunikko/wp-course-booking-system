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

  const bookedCards = document.querySelectorAll(".class-card.booked");
  const bookedSubjectClasses = [];

  bookedCards.forEach((card) => {
    card.classList.forEach((cls) => {
      if (cls.startsWith("subject-")) {
        bookedSubjectClasses.push(cls);
      }
    });
  });

  bookedSubjectClasses.forEach((subjectClass) => {
    const conflictCards = document.querySelectorAll(
      `.class-card.available.${subjectClass}`,
    );

    conflictCards.forEach((card) => {
      card.classList.remove("available");
      card.classList.add("course-conflict");
      card.style.border = "1px solid #ef2727";
      card.style.backgroundColor = "#eeeeee";
      card.style.opacity = "0.7";

      const badge = document.createElement("div");
      badge.className = "badge conflict";
      badge.innerText = "CONFLICT";

      const teacherDiv = card.querySelector(".class-teacher");
      if (teacherDiv) {
        teacherDiv.parentNode.insertBefore(badge, teacherDiv.nextSibling);
      }
      const badgeArea = card.querySelector(".week-options");
      if (badgeArea) {
        const inputs = card.querySelectorAll(".week-radio");
        inputs.forEach((input) => (input.disabled = true));
      }
    });
  });

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

///////////////////////////////////////////////////////////////////////////
//
///////////////////////////////////////////////////////////////////////////

document.addEventListener("DOMContentLoaded", function () {
  const radios = document.querySelectorAll(".week-radio");
  const clearBtn =
    document.querySelector(".action-buttons .btn-secondary") ||
    document.querySelector(".btn-secondary");
  const selectedListDiv = document.querySelector(".selected-list");

  function initBookedConflicts() {
    const bookedCards = document.querySelectorAll(".class-card.booked");
    const bookedSubjectClasses = [];
    const bookedSlots = [];

    bookedCards.forEach((card) => {
      card.classList.forEach((cls) => {
        if (cls.startsWith("subject-")) {
          bookedSubjectClasses.push(cls);
        }
      });

      let day = card.getAttribute("data-day");
      let time = card.getAttribute("data-time");

      if (!day || !time) {
        const radio = card.querySelector(".week-radio");
        if (radio) {
          day = radio.getAttribute("data-day");
          time = radio.getAttribute("data-time");
        }
      }

      if (day && time) {
        bookedSlots.push(day.trim() + "-" + time.trim());
      }
    });

    const availableCards = document.querySelectorAll(".class-card.available");

    availableCards.forEach((card) => {
      let shouldBlock = false;

      card.classList.forEach((cls) => {
        if (bookedSubjectClasses.includes(cls)) {
          shouldBlock = true;
        }
      });

      let currentDay = card.getAttribute("data-day");
      let currentTime = card.getAttribute("data-time");

      if (!currentDay || !currentTime) {
        const radio = card.querySelector(".week-radio");
        if (radio) {
          currentDay = radio.getAttribute("data-day");
          currentTime = radio.getAttribute("data-time");
        }
      }

      if (currentDay && currentTime) {
        const currentSlot = currentDay.trim() + "-" + currentTime.trim();
        if (bookedSlots.includes(currentSlot)) {
          shouldBlock = true;
        }
      }

      if (shouldBlock) {
        card.classList.remove("available");
        card.classList.add("init-conflict");
        card.style.border = "2px solid #ff9800";
        card.style.backgroundColor = "#fff9e6";
        card.style.opacity = "0.7";

        insertConflictBadge(card);
        card
          .querySelectorAll(".week-radio")
          .forEach((input) => (input.disabled = true));
      }
    });
  }

  function insertConflictBadge(card) {
    if (card.querySelector(".badge.conflict")) return;
    const badge = document.createElement("div");
    badge.className = "badge conflict";
    badge.innerText = "CONFLICT";

    const teacherDiv = card.querySelector(".class-teacher");
    if (teacherDiv) {
      teacherDiv.parentNode.insertBefore(badge, teacherDiv.nextSibling);
    }
  }

  function updateSelectedList() {
    if (!selectedListDiv) return;

    const checkedRadios = Array.from(radios).filter(
      (r) =>
        r.checked && !r.closest(".class-card").classList.contains("booked"),
    );

    selectedListDiv.innerHTML = "";

    if (checkedRadios.length === 0) {
      selectedListDiv.innerHTML =
        '<p class="no-selection" style="color: #999; font-size: 14px;">No classes selected.</p>';
      return;
    }

    checkedRadios.forEach((r) => {
      const subject = r.getAttribute("data-subject");
      const week = r.getAttribute("data-week");
      const day = r.getAttribute("data-day");
      const time = r.getAttribute("data-time");

      const item = document.createElement("div");
      item.className = "selected-item";
      item.style.padding = "6px 12px";
      item.style.marginBottom = "6px";
      item.style.backgroundColor = "#e7f3ff";
      item.style.borderLeft = "4px solid #007bff";
      item.style.borderRadius = "4px";
      item.style.fontSize = "13px";

      item.innerHTML = `<strong>${subject}</strong> - ${week} (${day} ${time})`;
      selectedListDiv.appendChild(item);
    });
  }

  function handleLiveConflicts() {
    const checkedRadios = Array.from(radios).filter(
      (r) =>
        r.checked && !r.closest(".class-card").classList.contains("booked"),
    );
    const activeCards = checkedRadios.map((r) => r.closest(".class-card"));

    const activeSubjects = checkedRadios.map((r) =>
      r.getAttribute("data-subject"),
    );
    const activeSlots = checkedRadios.map(
      (r) => r.getAttribute("data-day") + "-" + r.getAttribute("data-time"),
    );

    const staticBlockedCards = document.querySelectorAll(
      ".class-card.booked, .class-card.init-conflict",
    );
    staticBlockedCards.forEach((card) => {
      card.classList.forEach((cls) => {
        if (cls.startsWith("subject-")) {
          const sub =
            card.getAttribute("data-subject") ||
            (card.querySelector(".week-radio")
              ? card.querySelector(".week-radio").getAttribute("data-subject")
              : null);
          if (sub && !activeSubjects.includes(sub)) {
            activeSubjects.push(sub);
          }
        }
      });

      let d = card.getAttribute("data-day");
      let t = card.getAttribute("data-time");
      if (!d || !t) {
        const r = card.querySelector(".week-radio");
        if (r) {
          d = r.getAttribute("data-day");
          t = r.getAttribute("data-time");
        }
      }
      if (d && t) {
        const slotKey = d.trim() + "-" + t.trim();
        if (!activeSlots.includes(slotKey)) {
          activeSlots.push(slotKey);
        }
      }
    });

    radios.forEach((r) => {
      const card = r.closest(".class-card");

      if (
        card.classList.contains("booked") ||
        card.classList.contains("init-conflict")
      ) {
        return;
      }

      if (activeCards.includes(card)) {
        r.disabled = false;
        card.classList.remove("live-conflict");
        card.style.border = "2px solid #007bff";
        card.style.backgroundColor = "#e7f3ff";
        card.style.opacity = "1";

        const liveBadge = card.querySelector(".badge.conflict");
        if (liveBadge) liveBadge.remove();
        return;
      }

      const currentSubject = r.getAttribute("data-subject");
      const currentSlotKey =
        r.getAttribute("data-day") + "-" + r.getAttribute("data-time");

      if (
        activeSubjects.includes(currentSubject) ||
        activeSlots.includes(currentSlotKey)
      ) {
        r.disabled = true;
        card.classList.add("live-conflict");
        card.style.border = "2px solid rgb(242 203 146 / 88%);";
        card.style.backgroundColor = "white";
        card.style.opacity = "0.7";
        insertConflictBadge(card);
      } else {
        r.disabled = false;
        card.classList.remove("live-conflict");
        card.style.border = "";
        card.style.backgroundColor = "";
        card.style.opacity = "";

        const liveBadge = card.querySelector(".badge.conflict");
        if (liveBadge) liveBadge.remove();
      }
    });

    updateSelectedList();
  }

  radios.forEach((radio) => {
    radio.addEventListener("click", function (e) {
      if (this.closest(".class-card").classList.contains("booked")) return;

      if (this.classList.contains("is-checked")) {
        this.checked = false;
        this.classList.remove("is-checked");

        handleLiveConflicts();
      } else {
        const name = this.getAttribute("name");
        document
          .querySelectorAll(`input[name="${name}"]`)
          .forEach((r) => r.classList.remove("is-checked"));

        this.classList.add("is-checked");
      }
    });
  });

  if (clearBtn) {
    clearBtn.addEventListener("click", function (e) {
      e.preventDefault();

      radios.forEach((radio) => {
        if (!radio.closest(".class-card").classList.contains("booked")) {
          radio.checked = false;
          radio.classList.remove("is-checked");
        }
      });

      handleLiveConflicts();
      console.log("🧹 모든 선택 및 패널 리스트가 초기화되었습니다.");
    });
  }

  initBookedConflicts();
  handleLiveConflicts();

  radios.forEach((radio) => {
    radio.addEventListener("change", handleLiveConflicts);
  });
});
