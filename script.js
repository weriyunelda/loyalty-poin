let customers = [];
let filteredCustomers = [];
let currentDeleteId = null;

const tableBody = document.getElementById('customerTableBody');
const searchForm = document.getElementById('searchForm');
const searchInput = document.getElementById('searchInput');
const resetBtn = document.getElementById('resetBtn');
const searchMessage = document.getElementById('searchMessage');
const addCustomerBtn = document.getElementById('addCustomerBtn');
const customerModal = document.getElementById('customerModal');
const deleteCustomerModal = document.getElementById('deleteCustomerModal');
const customerForm = document.getElementById('customerForm');
const customerNameInput = document.getElementById('customerName');
const customerIdInput = document.getElementById('customerId');
const modeInput = document.getElementById('mode');
const modalTitle = document.getElementById('customerModalTitle');
const submitModalText = document.getElementById('submitModalText');
const cancelModalBtn = document.getElementById('cancelModalBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const deleteCustomerName = document.getElementById('deleteCustomerName');
const deleteCustomerId = document.getElementById('deleteCustomerId');

function renderCustomers() {
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (filteredCustomers.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="empty-state">Tidak ada data customer</td></tr>';
        return;
    }

    filteredCustomers.forEach((customer) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${customer.id}</strong></td>
            <td>${customer.nama}</td>
            <td><span class="poin-badge">${customer.totalPoin}</span></td>
            <td><span class="badge ${customer.level.toLowerCase()}">${customer.level}</span></td>
            <td>${customer.terakhirTransaksi}</td>
            <td class="action-cell">
                <a href="detail-loyalty.php?id=${customer.id}" class="btn btn-sm btn-primary">Detail</a>
                <button type="button" class="btn btn-sm btn-edit" data-action="edit" data-id="${customer.id}">Edit</button>
                <button type="button" class="btn btn-sm btn-delete" data-action="delete" data-id="${customer.id}">Hapus</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

async function loadCustomers() {
    const keyword = searchInput.value.trim();
    const url = keyword ? `api/customers.php?search=${encodeURIComponent(keyword)}` : 'api/customers.php';

    try {
        const response = await fetch(url);
        const result = await response.json();

        if (!result.success) {
            customers = [];
            filteredCustomers = [];
            renderCustomers();
            searchMessage.hidden = false;
            searchMessage.className = 'warning-box';
            searchMessage.textContent = result.message || 'Gagal memuat data customer';
            return;
        }

        customers = result.data || [];
        filteredCustomers = [...customers];
        renderCustomers();
        updateSearchMessage();
    } catch (error) {
        customers = [];
        filteredCustomers = [];
        renderCustomers();
        searchMessage.hidden = false;
        searchMessage.className = 'warning-box';
        searchMessage.textContent = 'Gagal terhubung ke server';
    }
}

function updateSearchMessage() {
    const keyword = searchInput.value.trim();

    if (!keyword) {
        searchMessage.hidden = true;
        searchMessage.textContent = '';
        return;
    }

    const resultCount = filteredCustomers.length;
    if (resultCount > 0) {
        searchMessage.hidden = false;
        searchMessage.className = 'info-box';
        searchMessage.innerHTML = `<i class="fas fa-info-circle"></i> Hasil pencarian untuk "<strong>${keyword}</strong>" (${resultCount} customer ditemukan)`;
    } else {
        searchMessage.hidden = false;
        searchMessage.className = 'warning-box';
        searchMessage.innerHTML = `<i class="fas fa-search"></i> Tidak ada customer ditemukan untuk "<strong>${keyword}</strong>"`;
    }
}

function filterCustomers(keyword) {
    const term = keyword.toLowerCase().trim();

    filteredCustomers = customers.filter((customer) => {
        return customer.nama.toLowerCase().includes(term) || customer.id.toLowerCase().includes(term);
    });

    renderCustomers();
    updateSearchMessage();
}

function openAddModal() {
    modeInput.value = 'add';
    customerIdInput.value = '';
    customerNameInput.value = '';
    modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Tambah Customer Baru';
    submitModalText.textContent = 'Tambah';
    customerModal.style.display = 'flex';
}

function openEditModal(id) {
    const customer = customers.find((item) => item.id === id);
    if (!customer) return;

    modeInput.value = 'edit';
    customerIdInput.value = customer.id;
    customerNameInput.value = customer.nama;
    modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Edit Customer';
    submitModalText.textContent = 'Simpan Perubahan';
    customerModal.style.display = 'flex';
}

function closeModal() {
    customerModal.style.display = 'none';
}

function openDeleteModal(id) {
    const customer = customers.find((item) => item.id === id);
    if (!customer) return;

    currentDeleteId = id;
    deleteCustomerName.textContent = customer.nama;
    deleteCustomerId.textContent = customer.id;
    deleteCustomerModal.style.display = 'flex';
}

function closeDeleteModal() {
    deleteCustomerModal.style.display = 'none';
    currentDeleteId = null;
}

searchForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    await loadCustomers();
});

resetBtn.addEventListener('click', async () => {
    searchInput.value = '';
    await loadCustomers();
});

addCustomerBtn.addEventListener('click', openAddModal);
cancelModalBtn.addEventListener('click', closeModal);
cancelDeleteBtn.addEventListener('click', closeDeleteModal);
confirmDeleteBtn.addEventListener('click', async () => {
    if (!currentDeleteId) return;

    try {
        const response = await fetch(`api/customers.php?id=${encodeURIComponent(currentDeleteId)}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) {
            alert(result.message || 'Gagal menghapus customer');
            return;
        }

        await loadCustomers();
        closeDeleteModal();
    } catch (error) {
        alert('Gagal terhubung ke server');
    }
});

customerForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const nama = customerNameInput.value.trim();
    if (!nama) return;

    const formData = new FormData();
    formData.append('action', modeInput.value);
    formData.append('nama', nama);

    if (modeInput.value === 'edit') {
        formData.append('id_customer', customerIdInput.value);
    }

    try {
        const response = await fetch('api/customers.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (!result.success) {
            alert(result.message || 'Gagal menyimpan customer');
            return;
        }

        await loadCustomers();
        closeModal();
    } catch (error) {
        alert('Gagal terhubung ke server');
    }
});

tableBody.addEventListener('click', (event) => {
    const button = event.target.closest('button');
    if (!button) return;

    const action = button.dataset.action;
    const id = button.dataset.id;

    if (action === 'edit') {
        openEditModal(id);
    } else if (action === 'delete') {
        openDeleteModal(id);
    }
});

window.addEventListener('click', (event) => {
    if (event.target === customerModal) closeModal();
    if (event.target === deleteCustomerModal) closeDeleteModal();
});

loadCustomers();
