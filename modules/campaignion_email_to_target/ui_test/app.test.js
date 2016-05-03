describe('app', function() {
  var setup = require('./test-helper.js').setup;
  var teardown = require('./test-helper.js').teardown;

  var Vue = require('vue');
  var app = require('../ui_src/app.vue');

  Vue.use(require('vue-dnd'));


  it('has initial data', function() {
    expect(typeof app.data).toBe('function');
    var data = app.data();
    expect(data.operators.has('==')).toBe(true);
    expect(data.operators.has('!=')).toBe(true);
    expect(data.operators.has('regexp')).toBe(true);
    expect(data.currentSpecIndex).toBe(-1);
    expect(Object.getOwnPropertyNames(data.currentSpec).length).toBe(0); // should not have properties of its own
    expect(data.modalDirty).toBe(false);
    expect(data.showSpecModal).toBe(false);
  });

  xdescribe('initialisation', function() {
    var vm, testData = require('./data/example-data.js')

    beforeAll(function() {
      vm = setup(app, testData)
    })

    afterAll(function() {
      teardown(vm)
    })

    it('parses bootstrapped data', function() {
      expect(vm.defaultMessage.message).toEqual(testData.messageSelection[testData.messageSelection.length - 1].message)
      expect(vm.specs.length).toBe(testData.messageSelection.length - 1)
      expect(vm.specs[0]).toEqual(jasmine.objectContaining(testData.messageSelection[0]))
      expect(vm.hardValidation).toBe(testData.hardValidation)
    });

    it('validates bootstrapped data', function() {
      expect(vm.specs[2].errors).toEqual(jasmine.arrayContaining(['This message won’t be sent. The same filter has been applied above.']))
    })

    it('creates filter strings for bootstrapped data', function() {
      expect(vm.specs[0].filterStr).toBeTruthy()
    })

    it('renders the form header', function() {
      expect(vm.$el.querySelector('h3').textContent).toBe('Message to all remaining targets')
    })

  })

  describe('Specification CRUD', function() {
    var vm, testData = require('./data/empty-data.js')

    beforeAll(function() {
      vm = setup(app, testData)
    })

    afterAll(function() {
      teardown(vm)
    })

    describe('open dialogue', function() {

      beforeAll(function(done) {
        vm.$el.querySelector('.add-message').click()
        setTimeout(done, 500);
      })

      it('populates currentSpec', function() {
        expect(vm.currentSpec.type).toBe('message-template')
        expect(vm.currentSpec.label).toBe('')
        expect(vm.currentSpec.filters.length).toBe(0)
        expect(vm.currentSpec.message).toEqual({
          subject: '',
          header: '',
          body: '',
          footer: ''
        })
      })

      it('shows "new message" dialogue', function(done) {
        expect(vm.currentSpecIndex).toBe(-1)
        expect(vm.modalDirty).toBe(false)
        expect(vm.showSpecModal).toBe(true)
        expect(vm.$el.querySelector('.modal h4').textContent).toBe('Add specific Message')
        expect(vm.$el.querySelector('.modal')).toHaveClass('in')
        done()
      })

    })

    it('disables save button for empty forms', function() {
      expect(vm.$el.querySelector('.modal-save')).toBeDisabled()
    })

    describe('save button', function() {
      beforeAll(function(done) {
        vm.currentSpec.message.subject = 'foo'
        vm.$nextTick(done)
      })

      it('enabled for forms with a message subject', function(done) {
        expect(vm.$el.querySelector('.modal-save')).not.toBeDisabled()
        done()
      })
    })


    it('saves a new message', function() {
      vm.currentSpec.label = 'bar'
      vm.$el.querySelector('.modal-save').click()

      expect(vm.specs.length).toBe(1)
      expect(vm.specs[0].label).toBe('bar')
      expect(vm.showSpecModal).toBe(false)
    })



    it('shows "new exclusion" dialogue')
    it('saves a new exclusion')

    it('opens dialogue to edit message')
    it('shows warning on close when dialogue has unsaved changes')
    it('saves changes to message')

    it('shows the applied filters')

    it('deletes a message')
  })


});
