import { ref } from 'vue';

export function useShake() {
  const shakeError = ref(false);
  let shakeTimeout = null;

  const triggerShake = () => {
    if (shakeTimeout) {
      clearTimeout(shakeTimeout);
      shakeError.value = false;
      setTimeout(() => {
        shakeError.value = true;
        shakeTimeout = setTimeout(() => {
          shakeError.value = false;
        }, 500);
      }, 10);
    } else {
      shakeError.value = true;
      shakeTimeout = setTimeout(() => {
        shakeError.value = false;
        shakeTimeout = null;
      }, 500);
    }
  };

  return {
    shakeError,
    triggerShake
  };
}
