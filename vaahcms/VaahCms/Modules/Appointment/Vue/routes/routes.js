let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctor from "./vue-routes-doctors";
import patient from "./vue-routes-patients";
import patientAppointment from "./vue-routes-patientappointments";

routes = routes.concat(dashboard);
routes = routes.concat(doctor);
routes = routes.concat(patient);
routes = routes.concat(patientAppointment);

export default routes;
