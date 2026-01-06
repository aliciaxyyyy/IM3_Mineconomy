/*script.js ============================================================================================================================*/
document.addEventListener("DOMContentLoaded", () => {
    const dropCells = document.querySelectorAll(".drop-cell");
    const draggableItems = document.querySelectorAll(".draggable-item");
    const goButton = document.querySelector(".go-button-minerals");
    const minecart = document.getElementById('minecart');
    const checkpoints = document.querySelectorAll('.checkpoint');
    let activePeriod = document.body.getAttribute('data-period') || "1";

    // -------------------------------
    // DRAG & DROP LOGIK
    // -------------------------------
    draggableItems.forEach(item => {
        item.addEventListener("dragstart", e => {
            e.dataTransfer.setData("mineral", item.dataset.mineral);
            e.dataTransfer.effectAllowed = "move";
        });
    });

    dropCells.forEach(cell => {
        cell.addEventListener("dragover", e => e.preventDefault());

        cell.addEventListener("drop", e => {
            e.preventDefault();
            const mineralType = e.dataTransfer.getData("mineral");
            const original = document.querySelector(`.draggable-item[data-mineral="${mineralType}"]`);
            if (!original) return;

            // Nur ein Mineral pro Zelle
            if (cell.children.length > 0) return;

            // Kein doppeltes Platzieren
            const alreadyPlaced = Array.from(dropCells).some(c => c.children.length && c.children[0].dataset.mineral === mineralType);
            if (alreadyPlaced) return;

            // Klon erstellen
            const clone = original.cloneNode(true);
            clone.draggable = false;
            clone.style.cursor = "pointer";
            clone.style.maxWidth = "150%";
            clone.style.maxHeight = "150%";

            // Klick auf Klon -> zurücksetzen
            clone.addEventListener("click", () => {
                cell.innerHTML = "";
                original.style.display = "inline-block";
            });

            // Original ausblenden
            original.style.display = "none";

            cell.appendChild(clone);
        });
    });

    // -------------------------------
    // MINECART & PERIODENWECHSEL
    // -------------------------------
    function setMinecartPosition(period) {
        const positions = { '1': '5%', '7': '48.5%', '30': '94.5%' };
        minecart.style.left = positions[period] || '0%';
    }    function setActiveCheckpoint(period) {
        checkpoints.forEach(cp => cp.classList.remove('active'));
        const activeCheckpoint = document.querySelector(`[data-period="${period}"]`);
        if (activeCheckpoint) activeCheckpoint.classList.add('active');
    }

    function moveMinecartToPeriod(period) {
        if (period === activePeriod) return;

        setMinecartPosition(period);
        setActiveCheckpoint(period);

        // Animation
        minecart.style.transform = 'translate(-50%, -70%)';
        setTimeout(() => {
            minecart.style.transform = 'translate(-50%, -50%)';
        }, 200);

        // Chart-Daten aktualisieren
        setTimeout(() => updateChartForPeriod(period), 300);
    }

    // Klick auf Checkpoints
    checkpoints.forEach(cp => {
        cp.addEventListener('click', () => {
            const period = cp.getAttribute('data-period');
            moveMinecartToPeriod(period);
        });
    });

    // Klick auf Minecart = nächster Zeitraum
    minecart.addEventListener('click', () => {
        const periods = ['1', '7', '30'];
        const nextPeriod = periods[(periods.indexOf(activePeriod) + 1) % periods.length];
        moveMinecartToPeriod(nextPeriod);
    });

    // -------------------------------
    // CHART UPDATE
    // -------------------------------
    function updateChartForPeriod(period) {
        const mineralsInCells = Array.from(dropCells)
            .map(cell => cell.children[0]?.dataset.mineral)
            .filter(Boolean);

        if (!mineralsInCells.length) {
            // Keine Mineralien → Chart leeren
            window.metalsChart.data.datasets = [];
            window.metalsChart.data.labels = [];
            window.metalsChart.update();
            activePeriod = period;
            return;
        }

        // Hole die Daten per AJAX
        fetch(`/php/api.php?period=${period}&ajax=1`)
            .then(res => res.json())
            .then(data => {
                if (!window.metalsChart) return;

                const newDatasets = [];

                if (mineralsInCells.includes("gold")) {
                    newDatasets.push({
                        label: 'Gold (USD)',
                        data: data.goldData,
                        borderColor: '#FFD700',
                        backgroundColor: 'rgba(255, 215, 0, 0.2)',
                        borderWidth: (period === "1") ? 2 : 2,
                        fill: (period === "1")
                    });
                }
                if (mineralsInCells.includes("silver")) {
                    newDatasets.push({
                        label: 'Silver (USD)',
                        data: data.silverData,
                        borderColor: '#C0C0C0',
                        backgroundColor: 'rgba(192, 192, 192, 0.2)',
                        borderWidth: (period === "1") ? 2 : 2,
                        fill: (period === "1")
                    });
                }
                if (mineralsInCells.includes("platinum")) {
                    newDatasets.push({
                        label: 'Platinum (USD)',
                        data: data.platinumData,
                        borderColor: '#E5E4E2',
                        backgroundColor: 'rgba(229, 228, 226, 0.2)',
                        borderWidth: (period === "1") ? 2 : 2,
                        fill: (period === "1")
                    });
                }

                window.metalsChart.data.datasets = newDatasets;
                window.metalsChart.data.labels = data.dates;
                window.metalsChart.config.type = (period === "1") ? "bar" : "line";
                window.metalsChart.options.plugins.title.text =
                    `Precious Metals Prices - Last ${period} > 1 ? 's' : ''} (USD per Ounce)`;
                window.metalsChart.update();

                activePeriod = period;
            })
            .catch(err => console.error(err));
    }

//   Chart initial leer
  const ctx = document.getElementById('metalsChart').getContext('2d');
  window.metalsChart = new Chart(ctx, {
      type: 'bar',
      data: { labels: [], datasets: [] },
      options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
              title: { 
                  display: true, 
                  text: 'Precious Metals Prices (USD per Ounce)', 
                  color: '#FFF7E9',
                  font: {
                      family: "Darumadrop One, sans-serif",
                      size: 22,
                      weight: 'normal'
                  }
              },
              legend: { 
                  labels: { 
                      color: '#FFF7E9',
                      font: {
                          family: "Darumadrop One, sans-serif",
                          size: 14
                      }
                  } 
              }
          },
          scales: {
              y: { 
                  beginAtZero: false, 
                  ticks: { 
                      color: '#FFF7E9',
                      font: {
                          family: "Darumadrop One, sans-serif",
                          size: 12
                      }
                  }, 
                  grid: { color: 'rgba(244, 231, 193, 0.1)' } 
              },
              x: { 
                  ticks: { 
                      color: '#FFF7E9',
                      font: {
                          family: "Darumadrop One, sans-serif",
                          size: 12
                      }
                  }, 
                  grid: { color: 'rgba(244, 231, 193, 0.1)' } 
              }
          }
      }
  });


    // -------------------------------
    // GO BUTTON
    // -------------------------------
    if (goButton) {
        goButton.addEventListener("click", () => updateChartForPeriod(activePeriod));
    }

    // Initial Setup
    setMinecartPosition(activePeriod);
    setActiveCheckpoint(activePeriod);
});

/*======================================================================================================================================*/
document.querySelector('.dropdown-toggle').addEventListener('click', function () {
    const container = document.querySelector('.mineral-info-container');
    container.classList.toggle('active'); // Toggle the "active" class
});


