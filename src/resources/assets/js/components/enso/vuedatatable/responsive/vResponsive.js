import resizeDetector from 'element-resize-detector';
import ResponsiveTable from './ResponsiveTable';

const erd = resizeDetector({ strategy: 'scroll' });

export default {
    componentUpdated: (el, binding, { context }) => {
        const table = new ResponsiveTable(el, context);
        table.resize();
        erd.removeAllListeners(el);
        erd.listenTo(el, () => table.resize());
    },
    unbind(el) {
        const erd = resizeDetector({ strategy: 'scroll' });
        erd.uninstall(el);
    },
};
