import {createApp, markRaw} from 'vue';
import { createPinia, PiniaVuePlugin  } from 'pinia'


//-------------PrimeVue Imports

import PrimeVue from "primevue/config";
import BadgeDirective from "primevue/badgedirective";
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice'
import Menu from 'primevue/menu';
import ProgressBar from 'primevue/progressbar';
import Ripple from 'primevue/ripple';
import StyleClass from 'primevue/styleclass';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';
import Sidebar from 'primevue/sidebar';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Card from 'primevue/card';
import Dialog from 'primevue/dialog';
import Chart from 'primevue/chart';
import Checkbox from 'primevue/checkbox';
import FileUpload from 'primevue/fileupload';
import Steps from "primevue/steps";
import VueMobileDetection from 'vue-mobile-detection';







//-------------/PrimeVue Imports

//-------------CRUD PrimeVue Imports

import Badge from "primevue/badge";
import Button from "primevue/button";
import Panel from "primevue/panel";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import InputSwitch from "primevue/inputswitch";
import DataTable from "primevue/datatable";
import Paginator from "primevue/paginator";
import Divider from "primevue/divider";
import RadioButton from "primevue/radiobutton";
import Message from "primevue/message";
import Tag from "primevue/tag";
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Dropdown from 'primevue/dropdown';
//-------------/CRUD PrimeVue Imports



//-------------APP
import App from './layouts/App.vue'
import router from './routes/router'

const app = createApp(App);

const pinia = createPinia();
pinia.use(({ store }) => {
    store.$router = markRaw(router)
});
app.use(pinia);
app.use(PiniaVuePlugin);
app.use(router);
//-------------/APP


//-------------PrimeVue Use
app.use(PrimeVue, { ripple: true });
app.use(ConfirmationService);
app.use(ToastService);
app.use(DialogService);

app.directive('tooltip', Tooltip);
app.directive('badge', BadgeDirective);
app.directive('ripple', Ripple);
app.directive('styleclass', StyleClass);


app.component('ConfirmDialog', ConfirmDialog);
app.component('Menu', Menu);
app.component('ProgressBar', ProgressBar);
app.component('Toast', Toast);
app.component('Sidebar', Sidebar);
app.component('TabView', TabView);
app.component('TabPanel', TabPanel);
app.component('Card', Card);
app.component('Chart', Chart);
app.component('Dialog', Dialog);
app.component('Checkbox', Checkbox);
app.component('FileUpload', FileUpload);
app.component('Steps', Steps);
app.use(VueMobileDetection);

//-------------/PrimeVue Use

// -------------CRUD PrimeVue Use

app.component('Badge', Badge);
app.component('Button', Button);
app.component('Panel', Panel);
app.component('RadioButton', RadioButton);
app.component('InputText', InputText);
app.component('InputSwitch', InputSwitch);
app.component('Column', Column);
app.component('Paginator', Paginator);
app.component('Divider', Divider);
app.component('DataTable', DataTable);
app.component('Message', Message);
app.component('Tag', Tag);
app.component('InputNumber', InputNumber);
app.component('Calendar', Calendar);
app.component('Dropdown', Dropdown);
//-------------/CRUD PrimeVue Use


app.mount('#appAppointment')


export { app }
