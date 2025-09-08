
import { reactive } from 'vue';

const state = reactive({
  toasts: [],
  nextId: 0,
});

// Update the function signature to accept a single object
// You can also add a default type like 'info'
function addToast({ title, description, type = 'info', duration = 5000 }) {
  const newToast = {
    id: state.nextId++,
    title,
    description,
    type,
    duration,
  };
  state.toasts.push(newToast);

  // Automatically remove the toast after its duration
  setTimeout(() => {
    removeToast(newToast.id);
  }, duration);
}

function removeToast(id: number) {
  const index = state.toasts.findIndex(t => t.id === id);
  if (index !== -1) {
    state.toasts.splice(index, 1);
  }
}

// You can keep these for convenience
const success = (title, description, duration) => addToast({ title, description, type: 'success', duration });
const error = (title, description, duration) => addToast({ title, description, type: 'error', duration });
const info = (title, description, duration) => addToast({ title, description, type: 'info', duration });

export default {
  state,
  addToast,
  removeToast,
  success,
  error,
  info
};
