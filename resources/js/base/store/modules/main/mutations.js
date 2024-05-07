import * as types from '../../mutation-types'

export default {

    /**
     *
     * @param state
     * @param checkedId
     */
    [ types.CHECKED_ID ] (state, checkedId) {
        // Set Archive data
        state.checkedId = checkedId
    },


}
