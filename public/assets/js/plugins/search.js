document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('inputBuscar');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let table = document.querySelector("table tbody");
            
            if (!table) return; 
            
            let rows = table.rows;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].classList.contains('actions-panel')) continue;

                let text = rows[i].textContent.toUpperCase();
                
                if (text.indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                    let nextRow = rows[i].nextElementSibling;
                    if (nextRow && nextRow.classList.contains('actions-panel')) {
                        nextRow.classList.remove('show');
                    }
                }
            }
        });
    }
});