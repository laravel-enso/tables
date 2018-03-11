import ResizeSensor from 'css-element-queries/src/ResizeSensor';
import ResponsiveTable from './ResponsiveTable';

export default {
    inserted: (el, binding, { context }) => {
        const table = new ResponsiveTable(el, context);
        table.resize();
        const sensor = new ResizeSensor(el, () => table.resize());
    },
};
