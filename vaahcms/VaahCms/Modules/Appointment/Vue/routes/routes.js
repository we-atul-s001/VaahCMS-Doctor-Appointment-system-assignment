let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctor from "./vue-routes-doctors";

routes = routes.concat(dashboard);
routes = routes.concat(doctor);

export default routes;
