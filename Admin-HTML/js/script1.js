document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById("addDocumentationModal");
    const addDocumentationBtn = document.getElementById("addDocumentationBtn");
    const closeModalBtn = document.getElementsByClassName("close")[0];
    const documentationForm = document.getElementById("documentationForm");
    const documentationList = document.getElementById("documentationList");

    let documentationData = JSON.parse(localStorage.getItem("documentationData")) || [];

    // Function to render documentation items
    function renderDocumentation() {
        documentationList.innerHTML = "";
        documentationData.forEach((doc, index) => {
            const docItem = document.createElement("div");
            docItem.classList.add("documentation-item");

            docItem.innerHTML = `
                <img src="${doc.image}" alt="${doc.title}">
                <div class="documentation-details">
                    <h3>${doc.title}</h3>
                    <p>${doc.description}</p>
                    <p>${doc.price}</p>
                    <button class="btn-edit" onclick="editDocumentation(${index})">Edit</button>
                    <button class="btn-delete" onclick="deleteDocumentation(${index})">Delete</button>
                </div>
            `;
            documentationList.appendChild(docItem);
        });
    }

    // Function to add new documentation
    documentationForm.onsubmit = function (event) {
        event.preventDefault();

        const docImage = document.getElementById("docImage").files[0];
        const docTitle = document.getElementById("docTitle").value;
        const docDescription = document.getElementById("docDescription").value;
        

        if (!docImage || !docTitle || !docDescription || !docPrice) {
            alert("Please fill out all fields.");
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const newDoc = {
                image: e.target.result,
                title: docTitle,
                description: docDescription,
            };

            documentationData.push(newDoc);
            localStorage.setItem("documentationData", JSON.stringify(documentationData));
            renderDocumentation();
            modal.style.display = "none";
            documentationForm.reset();
        };
        reader.readAsDataURL(docImage);
    };

    // Function to edit documentation
    window.editDocumentation = function (index) {
        const doc = documentationData[index];
        document.getElementById("docTitle").value = doc.title;
        document.getElementById("docDescription").value = doc.description;
        document.getElementById("docPrice").value = doc.price;

        modal.style.display = "block";
        documentationForm.onsubmit = function (event) {
            event.preventDefault();

            doc.title = document.getElementById("docTitle").value;
            doc.description = document.getElementById("docDescription").value;
            doc.price = document.getElementById("docPrice").value;

            localStorage.setItem("documentationData", JSON.stringify(documentationData));
            renderDocumentation();
            modal.style.display = "none";
            documentationForm.reset();
        };
    };

    // Function to delete documentation
    window.deleteDocumentation = function (index) {
        documentationData.splice(index, 1);
        localStorage.setItem("documentationData", JSON.stringify(documentationData));
        renderDocumentation();
    };

    // Open modal when "Add New Documentation" button is clicked
    addDocumentationBtn.onclick = function () {
        modal.style.display = "block";
    };

    // Close modal when the close button (x) is clicked
    closeModalBtn.onclick = function () {
        modal.style.display = "none";
    };

    // Close modal when clicking outside the modal
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    // Render initial documentation
    renderDocumentation();
});