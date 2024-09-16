let routes= [];
let routes_list= [];

import List from '../pages/patientappointments/List.vue'
import Form from '../pages/patientappointments/Form.vue'
import Item from '../pages/patientappointments/Item.vue'

routes_list = {

    path: '/patientappointments',
    name: 'patientappointments.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'patientappointments.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'patientappointments.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;

