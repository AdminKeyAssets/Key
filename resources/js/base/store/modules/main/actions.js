import * as actionTypes from '../../action-types'
import * as mutationTypes from '../../mutation-types'

import { getData } from '../../../mixins/getData'

export default {

    /**
     *
     * @param context
     * @param data
     */
    [ actionTypes.CHECKED_ID ] (context, data) {
        context.commit(mutationTypes.CHECKED_ID, data)
    },


}
