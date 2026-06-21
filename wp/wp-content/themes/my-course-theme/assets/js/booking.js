document.addEventListener("DOMContentLoaded", function () {
  let selected = [];

  const cards = document.querySelectorAll(".class-card");
  const panel = document.querySelector(".selected-list");

  function getId(card) {
    return `${card.dataset.time}-${card.dataset.day}-${card.dataset.subject}`;
  }

  function renderPanel() {
    panel.innerHTML = "";

    selected.forEach((item) => {
      const div = document.createElement("div");
      div.className = "selected-item";

      div.innerHTML = `
                <span>${item.subject} - ${item.teacher} (${item.time}, ${item.day})</span>
                <button class="remove-btn" data-id="${item.id}">Remove</button>
            `;

      panel.appendChild(div);
    });
  }

  function addItem(card) {
    selected.push({
      id: getId(card),
      time: card.dataset.time,
      day: card.dataset.day,
      subject: card.dataset.subject,
      teacher: card.dataset.teacher,
    });
  }

  function removeItem(card) {
    const id = getId(card);
    selected = selected.filter((i) => i.id !== id);
  }

  function submitBooking() {
    fetch("/wp-admin/admin-ajax.php", {
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

        alert("Booking sent successfully!");
      })
      .catch((err) => {
        console.error(err);
        alert("Error submitting booking");
      });
  }

  cards.forEach((card) => {
    if (
      card.classList.contains("booked") ||
      card.classList.contains("conflict")
    ) {
      return;
    }

    card.addEventListener("click", function () {
      const id = getId(this);
      const exists = selected.find((i) => i.id === id);

      if (exists) {
        this.classList.remove("selected");
        removeItem(this);
      } else {
        this.classList.add("selected");
        addItem(this);
      }

      renderPanel();
    });
  });

  document
    .querySelector(".btn-primary")
    .addEventListener("click", submitBooking);
});
