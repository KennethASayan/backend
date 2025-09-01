// @/components/ui/Sidebar/sidebarState.ts

import { ref } from 'vue';

// State to determine if the sidebar should be visible on mobile
export const isSidebarVisible = ref(false);

// State to determine if the sidebar should be minimized on desktop
export const isSidebarMinimized = ref(false);

/**
 * Toggles the sidebar's state.
 * On desktop, it toggles the minimized state.
 * On mobile, it toggles the overall visibility.
 */
export const toggleSidebarVisibility = () => {
  if (window.innerWidth >= 768) {
    // Desktop behavior: toggle minimized state
    isSidebarMinimized.value = !isSidebarMinimized.value;
  } else {
    // Mobile behavior: toggle visibility
    isSidebarVisible.value = !isSidebarVisible.value;
  }
};

/**
 * Sets the initial sidebar state based on screen size.
 * This is useful for component mounting and window resizing.
 */
export const checkScreenSize = () => {
  if (window.innerWidth >= 768) {
    // On desktop, the sidebar is visible and not minimized by default
    isSidebarVisible.value = true;
    isSidebarMinimized.value = false;
  } else {
    // On mobile, the sidebar is hidden by default
    isSidebarVisible.value = false;
    isSidebarMinimized.value = false;
  }
};
