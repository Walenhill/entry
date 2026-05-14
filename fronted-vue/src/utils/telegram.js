const getTg = () => window.Telegram?.WebApp;

export const isTMA = () => {
  const tg = getTg();
  return !!tg && !!tg.initData;
};

export const getInitData = () => {
  return getTg()?.initData || '';
};

export const getInitDataUnsafe = () => {
  return getTg()?.initDataUnsafe || {};
};

export const getUser = () => {
  return getInitDataUnsafe()?.user || null;
};

export const getUserId = () => {
  const user = getUser();
  return user ? user.id : null;
};

export const showMainButton = (text, onClickCallback) => {
  if (!isTMA()) return;
  const tg = getTg();
  tg.MainButton.setText(text);
  tg.MainButton.show();
  tg.MainButton.onClick(onClickCallback);
};

export const hideMainButton = (onClickCallback) => {
  if (!isTMA()) return;
  const tg = getTg();
  tg.MainButton.hide();
  if (onClickCallback) {
    tg.MainButton.offClick(onClickCallback);
  }
};

export const enableMainButton = () => {
  if (isTMA()) {
    const tg = getTg();
    tg.MainButton.enable();
    tg.MainButton.setParams({ color: tg.themeParams.button_color || '#2481cc' });
  }
};

export const disableMainButton = () => {
  if (isTMA()) {
    const tg = getTg();
    tg.MainButton.disable();
    tg.MainButton.setParams({ color: '#cccccc' });
  }
};

export const hapticFeedback = {
  success() {
    if (isTMA()) getTg().HapticFeedback.notificationOccurred('success');
  },
  error() {
    if (isTMA()) getTg().HapticFeedback.notificationOccurred('error');
  },
  warning() {
    if (isTMA()) getTg().HapticFeedback.notificationOccurred('warning');
  },
  impact(style = 'light') {
    if (isTMA()) getTg().HapticFeedback.impactOccurred(style);
  }
};

export const expandApp = () => {
    const tg = getTg();
    if (isTMA() && !tg.isExpanded) {
        tg.expand();
    }
};

export const initTelegramApp = () => {
  if (isTMA()) {
    const tg = getTg();
    tg.ready();
    expandApp();

    document.documentElement.style.setProperty('--bg-main', tg.themeParams.bg_color || '#ffffff');
    document.documentElement.style.setProperty('--text-primary', tg.themeParams.text_color || '#000000');
    document.documentElement.style.setProperty('--bg-surface', tg.themeParams.secondary_bg_color || '#f4f4f5');
    document.documentElement.style.setProperty('--text-secondary', tg.themeParams.hint_color || '#9ca3af');
    document.documentElement.style.setProperty('--accent-primary', tg.themeParams.button_color || '#2481cc');
    document.documentElement.style.setProperty('--accent-primary-hover', tg.themeParams.button_color || '#2481cc');
  }
};
