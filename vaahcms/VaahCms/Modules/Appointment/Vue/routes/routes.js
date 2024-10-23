let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctor from "./vue-routes-doctors";
import patient from "./vue-routes-patients";
import appointment from "./vue-routes-appointments";
import appointmentmobile from "./vue-routes-appointmentmobile";

routes = routes.concat(dashboard);
routes = routes.concat(doctor);
routes = routes.concat(patient);
routes = routes.concat(appointment);
routes = routes.concat(appointmentmobile);

export default routes;
