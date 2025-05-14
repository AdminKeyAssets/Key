import DeveloperSave from './partials/DeveloperSave.vue';
import DeveloperView from './partials/DeveloperView.vue';

export default {
    components: {
        'developer-save': DeveloperSave,
    },
    props: ['id', 'getSaveDataRoute'],
    render(h) {
        return h(DeveloperSave, {
            props: {
                id: this.id,
                getSaveDataRoute: this.getSaveDataRoute
            }
        });
    }
}
