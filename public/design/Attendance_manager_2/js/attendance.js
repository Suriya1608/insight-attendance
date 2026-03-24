// ===== Attendance Data =====
let attendanceData = [
    {
        id: 1,
        name: "Olivia Rhye",
        department: "Design Team",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:55 AM",
        clockOut: null,
        totalHours: "4h 12m",
        minutesWorked: 252
    },
    {
        id: 2,
        name: "Phoenix Baker",
        department: "Engineering",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "09:00 AM",
        clockOut: null,
        totalHours: "4h 07m",
        minutesWorked: 247
    },
    {
        id: 3,
        name: "Lana Steiner",
        department: "Product",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "absent",
        clockIn: null,
        clockOut: null,
        totalHours: null,
        minutesWorked: 0
    },
    {
        id: 4,
        name: "Candice Wu",
        department: "Marketing",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "leave",
        clockIn: null,
        clockOut: null,
        totalHours: null,
        minutesWorked: 0
    },
    {
        id: 5,
        name: "Natali Craig",
        department: "Support",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "late",
        clockIn: "09:15 AM",
        clockOut: null,
        totalHours: "3h 52m",
        minutesWorked: 232
    },
    {
        id: 6,
        name: "Drew Cano",
        department: "Engineering",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:50 AM",
        clockOut: null,
        totalHours: "4h 17m",
        minutesWorked: 257
    },
    {
        id: 7,
        name: "Orlando Diggs",
        department: "Product",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "late",
        clockIn: "09:10 AM",
        clockOut: null,
        totalHours: "3h 57m",
        minutesWorked: 237
    },
    {
        id: 8,
        name: "Andi Lane",
        department: "Design Team",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:58 AM",
        clockOut: null,
        totalHours: "4h 09m",
        minutesWorked: 249
    },
    {
        id: 9,
        name: "Kate Morrison",
        department: "Marketing",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "09:02 AM",
        clockOut: null,
        totalHours: "4h 05m",
        minutesWorked: 245
    },
    {
        id: 10,
        name: "Koray Okumus",
        department: "Support",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:45 AM",
        clockOut: null,
        totalHours: "4h 22m",
        minutesWorked: 262
    },
    {
        id: 11,
        name: "Demi Wilkinson",
        department: "Engineering",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:52 AM",
        clockOut: null,
        totalHours: "4h 15m",
        minutesWorked: 255
    },
    {
        id: 12,
        name: "Alec Whitten",
        department: "Product",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "late",
        clockIn: "09:20 AM",
        clockOut: null,
        totalHours: "3h 47m",
        minutesWorked: 227
    },
    {
        id: 13,
        name: "Ryan Phillips",
        department: "Design Team",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:57 AM",
        clockOut: null,
        totalHours: "4h 10m",
        minutesWorked: 250
    },
    {
        id: 14,
        name: "Jade Hudson",
        department: "Marketing",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "09:01 AM",
        clockOut: null,
        totalHours: "4h 06m",
        minutesWorked: 246
    },
    {
        id: 15,
        name: "Marcus Grant",
        department: "Engineering",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "present",
        clockIn: "08:48 AM",
        clockOut: null,
        totalHours: "4h 19m",
        minutesWorked: 259
    },
    {
        id: 16,
        name: "Sofia Chen",
        department: "Support",
        avatar: "https://lh3.googleusercontent.com/aida-public/AB6AXuAI10gVWkLK0d0aSjP5IZfv9Isyf5hiRmCzHHAKeRuILpE9uNhEdJzF1mfUTKVl7dYF_zqr0n_NgZxAxlelJiqHI-PCdoLWgUnLxyKzx9JOESXOYYJAdx_bRNkmlORRmkl52UDiOqAQLqDxIjLtmoOz1pP6Rh1MDSyK39OTK7YsMILDKEYBtG2wp_9CO6aYRRWa1HfztS05Nc27S5oCMwl0UU0p9L4KDqUfDeOfqKzuSxaWEWfuQHOv3JvU8UWU_qZFyYJnOE16nrl_",
        status: "leave",
        clockIn: null,
        clockOut: null,
        totalHours: null,
        minutesWorked: 0
    }
];

// State Management
let currentPage = 1;
const itemsPerPage = 5;
let currentSort = { field: null, direction: 'asc' };
let currentFilters = { status: 'all', department: 'all' };

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    updateCurrentDate();
    renderTable();
    updateStats();
    
    // Update time every minute
    setInterval(updateCurrentDate, 60000);
});

// Event Listeners
function setupEventListeners() {
    // Mobile menu
    document.getElementById('menuBtn')?.addEventListener('click', toggleMenu);
    document.getElementById('mobileOverlay')?.addEventListener('click', toggleMenu);
    
    // Search
    document.getElementById('searchInput')?.addEventListener('input', handleSearch);
    
    // Pagination
    document.getElementById('prevBtn')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    
    document.getElementById('nextBtn')?.addEventListener('click', () => {
        const filtered = getFilteredData();
        const totalPages = Math.ceil(filtered.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
    
    // Sort headers
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', function() {
            const sortField = this.getAttribute('data-sort');
            handleSort(sortField);
        });
    });
    
    // Filter modal
    document.getElementById('filterBtn')?.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('filterModal'));
        modal.show();
    });
    
    document.getElementById('applyFilters')?.addEventListener('click', applyFilters);
    document.getElementById('clearFilters')?.addEventListener('click', clearFilters);
    
    // Export
    document.getElementById('exportBtn')?.addEventListener('click', exportData);
}

function toggleMenu() {
    document.querySelector('.sidebar')?.classList.toggle('active');
    document.getElementById('mobileOverlay')?.classList.toggle('active');
    document.body.style.overflow = document.querySelector('.sidebar')?.classList.contains('active') ? 'hidden' : '';
}

function updateCurrentDate() {
    const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
    const dateStr = new Date().toLocaleDateString('en-US', options);
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        dateEl.textContent = dateStr;
    }
}

function getFilteredData() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    
    return attendanceData.filter(employee => {
        const matchesSearch = employee.name.toLowerCase().includes(searchTerm) ||
                             employee.department.toLowerCase().includes(searchTerm);
        const matchesStatus = currentFilters.status === 'all' || employee.status === currentFilters.status;
        const matchesDepartment = currentFilters.department === 'all' || 
                                 employee.department.toLowerCase() === currentFilters.department.toLowerCase();
        
        return matchesSearch && matchesStatus && matchesDepartment;
    });
}

function getSortedData(data) {
    if (!currentSort.field) return data;
    
    return [...data].sort((a, b) => {
        let aVal, bVal;
        
        if (currentSort.field === 'name') {
            aVal = a.name;
            bVal = b.name;
        } else if (currentSort.field === 'status') {
            aVal = a.status;
            bVal = b.status;
        } else if (currentSort.field === 'hours') {
            aVal = a.minutesWorked;
            bVal = b.minutesWorked;
        }
        
        if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
        if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
        return 0;
    });
}

function renderTable() {
    const tbody = document.getElementById('attendanceTableBody');
    if (!tbody) return;
    
    const filtered = getFilteredData();
    const sorted = getSortedData(filtered);
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageData = sorted.slice(start, end);
    
    tbody.innerHTML = '';
    
    if (pageData.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    No employees found
                </td>
            </tr>
        `;
    } else {
        pageData.forEach(employee => {
            tbody.innerHTML += createTableRow(employee);
        });
    }
    
    updatePagination(filtered.length);
}

function createTableRow(emp) {
    const statusClass = `status-${emp.status}`;
    const statusLabel = {
        'present': 'Present',
        'absent': 'Absent',
        'leave': 'On Leave',
        'late': 'Late'
    }[emp.status];
    
    return `
        <tr>
            <td>
                <div class="employee-info">
                    <div class="employee-avatar" style="background-image: url('${emp.avatar}');"></div>
                    <div>
                        <div class="employee-name">${emp.name}</div>
                        <div class="employee-department">${emp.department}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="status-badge ${statusClass}">
                    <span class="status-dot"></span>
                    ${statusLabel}
                </span>
            </td>
            <td>
                <span class="time-value ${emp.status === 'late' ? 'time-late' : ''} ${!emp.clockIn ? 'time-empty' : ''}">
                    ${emp.clockIn || '-- : --'}
                </span>
            </td>
            <td>
                <span class="time-value ${!emp.clockOut ? 'time-empty' : ''}">
                    ${emp.clockOut || '-- : --'}
                </span>
            </td>
            <td>
                <span class="time-value ${!emp.totalHours ? 'time-empty' : ''}">
                    ${emp.totalHours || '--'}
                </span>
            </td>
            <td class="text-end">
                <button class="btn-action" title="More options">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </td>
        </tr>
    `;
}

function updatePagination(totalItems) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const start = (currentPage - 1) * itemsPerPage + 1;
    const end = Math.min(currentPage * itemsPerPage, totalItems);
    
    const infoEl = document.getElementById('paginationInfo');
    if (infoEl) {
        infoEl.textContent = `Showing ${start} to ${end} of ${totalItems} results`;
    }
    
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages || totalPages === 0;
}

function handleSearch() {
    currentPage = 1;
    renderTable();
}

function handleSort(field) {
    if (currentSort.field === field) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.field = field;
        currentSort.direction = 'asc';
    }
    renderTable();
}

function applyFilters() {
    const statusFilter = document.getElementById('statusFilter')?.value || 'all';
    const departmentFilter = document.getElementById('departmentFilter')?.value || 'all';
    
    currentFilters = {
        status: statusFilter,
        department: departmentFilter
    };
    
    currentPage = 1;
    renderTable();
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
    if (modal) modal.hide();
}

function clearFilters() {
    currentFilters = { status: 'all', department: 'all' };
    
    const statusFilter = document.getElementById('statusFilter');
    const departmentFilter = document.getElementById('departmentFilter');
    
    if (statusFilter) statusFilter.value = 'all';
    if (departmentFilter) departmentFilter.value = 'all';
    
    currentPage = 1;
    renderTable();
}

function updateStats() {
    const present = attendanceData.filter(e => e.status === 'present').length;
    const leave = attendanceData.filter(e => e.status === 'leave').length;
    const absent = attendanceData.filter(e => e.status === 'absent').length;
    const late = attendanceData.filter(e => e.status === 'late').length;
    
    const presentEl = document.getElementById('presentCount');
    const leaveEl = document.getElementById('leaveCount');
    const absentEl = document.getElementById('absentCount');
    const lateEl = document.getElementById('lateCount');
    
    if (presentEl) presentEl.textContent = present;
    if (leaveEl) leaveEl.textContent = leave;
    if (absentEl) absentEl.textContent = absent;
    if (lateEl) lateEl.textContent = late;
}