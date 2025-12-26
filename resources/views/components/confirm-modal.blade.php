<!-- Confirm Modal Component -->
<div id="confirmModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="closeConfirmModal(event)">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" onclick="event.stopPropagation()">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="mt-4 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="confirmModalTitle">
                    Xác nhận
                </h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-600 dark:text-gray-400" id="confirmModalMessage">
                        Bạn có chắc chắn muốn thực hiện hành động này?
                    </p>
                </div>
                <div class="flex justify-center gap-3 px-4 py-3">
                    <button onclick="closeConfirmModal()" type="button"
                        class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                        Hủy
                    </button>
                    <button onclick="confirmAction()" type="button" id="confirmButton"
                        class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Xác nhận
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;
let confirmForm = null;

function showConfirmModal(message, callback, title = 'Xác nhận', buttonText = 'Xác nhận', buttonColor = 'red') {
    confirmCallback = callback;
    document.getElementById('confirmModalTitle').textContent = title;
    document.getElementById('confirmModalMessage').textContent = message;
    document.getElementById('confirmButton').textContent = buttonText;
    
    const confirmButton = document.getElementById('confirmButton');
    confirmButton.className = confirmButton.className.replace(/bg-\w+-\d+/g, `bg-${buttonColor}-600`);
    confirmButton.className = confirmButton.className.replace(/hover:bg-\w+-\d+/g, `hover:bg-${buttonColor}-700`);
    confirmButton.className = confirmButton.className.replace(/ring-\w+-\d+/g, `ring-${buttonColor}-500`);
    
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirmModal(event) {
    if (!event || event.target.id === 'confirmModal') {
        document.getElementById('confirmModal').classList.add('hidden');
        confirmCallback = null;
        confirmForm = null;
    }
}

function confirmAction() {
    if (confirmCallback) {
        confirmCallback();
    }
    if (confirmForm) {
        confirmForm.submit();
    }
    closeConfirmModal();
}

function confirmDelete(form, message = 'Bạn có chắc chắn muốn xóa?') {
    confirmForm = form;
    showConfirmModal(message, null, 'Xác nhận xóa', 'Xóa', 'red');
    return false;
}

function confirmUpdate(form, message = 'Bạn có chắc chắn muốn cập nhật?') {
    confirmForm = form;
    showConfirmModal(message, null, 'Xác nhận cập nhật', 'Cập nhật', 'blue');
    return false;
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeConfirmModal();
    }
});
</script>
