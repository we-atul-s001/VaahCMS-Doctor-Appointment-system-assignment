let routes= [];
let routes_list= [];

import List from '../pages/appointmentmobile/List.vue'

routes_list = {

    path: '/appointmentmobile',
    name: 'appointmentmobile.index',
    component: List,
    props: false,
};

routes.push(routes_list);

export default routes;

