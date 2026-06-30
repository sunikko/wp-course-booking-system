document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-by');
    const subjectSelect = document.getElementById('filter-subject');
    const coursesGrid = document.getElementById('courses-grid');

    if (!sortSelect || !subjectSelect || !coursesGrid) return;

    const courseCards = Array.from(coursesGrid.querySelectorAll('.course-card'));

    // Collect unique subjects from cards and populate the select
    function populateSubjectFilter() {
        const subjects = new Set();
        
        courseCards.forEach(card => {
            const subject = card.dataset.subject;
            if (subject) {
                subjects.add(subject);
            }
        });
        
        const sortedSubjects = Array.from(subjects).sort();
        
        sortedSubjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject;
            option.textContent = subject;
            subjectSelect.appendChild(option);
        });
    }

    function applyFiltersAndSort() {
        const selectedSubject = subjectSelect.value;
        const sortValue = sortSelect.value;
        
        let filteredCards = [...courseCards];
        
        // Apply subject filter
        if (selectedSubject !== 'all') {
            filteredCards = filteredCards.filter(card => {
                const cardSubject = card.dataset.subject;
                return cardSubject === selectedSubject;
            });
        }
        
        // Apply sorting
        let sortedCards = [...filteredCards];
        
        switch(sortValue) {
            case 'price_low':
                sortedCards.sort((a, b) => {
                    const priceA = parseInt(a.dataset.price) || 0;
                    const priceB = parseInt(b.dataset.price) || 0;
                    return priceA - priceB;
                });
                break;

            case 'price_high':
                sortedCards.sort((a, b) => {
                    const priceA = parseInt(a.dataset.price) || 0;
                    const priceB = parseInt(b.dataset.price) || 0;
                    return priceB - priceA;
                });
                break;

            case 'capacity':
                sortedCards.sort((a, b) => {
                    const capA = parseInt(a.dataset.capacity) || 0;
                    const capB = parseInt(b.dataset.capacity) || 0;
                    return capB - capA;
                });
                break;

            case 'date':
            default:
                sortedCards.sort((a, b) => {
                    const idA = parseInt(a.dataset.courseId) || 0;
                    const idB = parseInt(b.dataset.courseId) || 0;
                    return idB - idA;
                });
                break;
        }

        // Reorder cards
        coursesGrid.innerHTML = '';
        sortedCards.forEach(card => coursesGrid.appendChild(card));
        
        // Show no courses message if needed
        if (sortedCards.length === 0) {
            coursesGrid.innerHTML = `
                <div class="no-courses" style="grid-column: 1/-1; text-align: center;">
                    <p>😕 No courses found for this subject.</p>
                    <a href="#" onclick="document.getElementById('filter-subject').value='all'; document.getElementById('filter-subject').dispatchEvent(new Event('change')); return false;" class="btn-cta">View All Courses</a>
                </div>
            `;
        }
    }

    // Initialize
    populateSubjectFilter();
    applyFiltersAndSort();
    
    sortSelect.addEventListener('change', applyFiltersAndSort);
    subjectSelect.addEventListener('change', applyFiltersAndSort);
});